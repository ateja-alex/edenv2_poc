#!/usr/bin/env python3
"""
Construit les parcours rejoues par k6 a partir de la table log_page d'EDEN.

Entree (stdin) : TSV "date<TAB>utilisateur_id<TAB>url", trie par utilisateur puis date,
                 produit par la requete SQL affichee avec --sql.
Sortie (stdout) : JSON {"source", "periode", "sessions": [[[delai_s, url], ...], ...]}

Seuls les GET sont rejoues (log_page ne garde pas le corps des POST), sans ceux qui
ecrivent en base (suppression, transformation, parametrage...).
Le resultat contient de vrais identifiants de fiches : le garder dans .local/ (ignore).

  mariadb -N -B <base> -e "$(python3 charge/extraire_parcours.py --sql --depuis 2026-07-01)" \
    | python3 charge/extraire_parcours.py > .local/tsi/parcours.json
"""
import argparse
import json
import re
import sys
from datetime import datetime

# GET qui modifient des donnees, ou hors usage normal : jamais rejoues
EXCLUS = re.compile(
    r"supprim|transformer|post_enregistrement|enregistrer|mise_a_jour|/maj_|dupliqu"
    r"|/parametrage/|/maintenance/|/cron/|deconnexion|logout|/login",
    re.IGNORECASE,
)
PAUSE_NOUVELLE_SESSION = 30 * 60   # s : au-dela, l'utilisateur a quitte l'application
DELAI_MAX = 300                    # s : une pause plus longue est ramenee a 5 min
PAS_MIN = 3                        # pages minimum pour garder une session


def sql(depuis, jusqu_a):
    return (
        "SELECT date, utilisateur_id, url FROM log_page "
        f"WHERE methode = 'GET' AND date >= '{depuis}' AND date < '{jusqu_a}' "
        "ORDER BY utilisateur_id, date, id"
    )


def main():
    p = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    p.add_argument("--sql", action="store_true", help="affiche la requete SQL a lancer, puis sort")
    p.add_argument("--depuis", default="2026-07-01", help="date de debut (incluse)")
    p.add_argument("--jusqu-a", default="2100-01-01", help="date de fin (exclue)")
    args = p.parse_args()

    if args.sql:
        print(sql(args.depuis, args.jusqu_a))
        return

    sessions, courante = [], []
    utilisateur_prec, date_prec = None, None
    debut = fin = None

    for ligne in sys.stdin:
        champs = ligne.rstrip("\n").split("\t")
        if len(champs) != 3:
            continue
        date_txt, utilisateur, url = champs
        try:
            date = datetime.strptime(date_txt, "%Y-%m-%d %H:%M:%S")
        except ValueError:
            continue
        if EXCLUS.search(url):
            continue

        nouvelle = (
            utilisateur != utilisateur_prec
            or date_prec is None
            or (date - date_prec).total_seconds() > PAUSE_NOUVELLE_SESSION
        )
        if nouvelle:
            if len(courante) >= PAS_MIN:
                sessions.append(courante)
            courante, delai = [], 0
        else:
            delai = min(int((date - date_prec).total_seconds()), DELAI_MAX)

        courante.append([delai, url])
        utilisateur_prec, date_prec = utilisateur, date
        debut = date if debut is None or date < debut else debut
        fin = date if fin is None or date > fin else fin

    if len(courante) >= PAS_MIN:
        sessions.append(courante)

    json.dump(
        {
            "source": "log_page",
            "periode": [str(debut), str(fin)],
            "sessions": sessions,
        },
        sys.stdout,
        ensure_ascii=False,
        separators=(",", ":"),
    )
    pas = sum(len(s) for s in sessions)
    print(f"{len(sessions)} sessions, {pas} requetes GET ({debut} -> {fin})", file=sys.stderr)


if __name__ == "__main__":
    main()
