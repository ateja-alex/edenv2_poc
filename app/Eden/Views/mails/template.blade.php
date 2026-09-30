<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
   <meta charset="utf-8">
   <title></title>
   <link href="https://fonts.googleapis.com/css?family=Gloria+Hallelujah" rel="stylesheet">
   <link href="https://fonts.googleapis.com/css?family=Raleway:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i|Source+Sans+Pro:200,200i,300,300i,400,400i,600,600i,700,700i,900,900i" rel="stylesheet">
</head>


<body style="color: #606060; font-family: 'Source Sans Pro', sans-serif; font-size: 18px; margin: auto;">
   @include('eden::mails.header')
   @yield('contenu')
   @include('eden::mails.footer')
</body>
</html>
