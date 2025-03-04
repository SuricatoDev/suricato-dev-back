    <!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Excursionistas - Sistema de Gerenciamento de Caravanas</title>
</head>

<body>

    <p>Prezado(a) {{$user->name}}</p>

    <p>Para recuperar a sua senha do App Excursionistas, use o código de verificação abaixo:</p>

    <p><strong>{{ $code }}</strong></p>

    <p>Por questões de segurança esse código é válido somente até as {{ $formattedTime }} do dia {{ $formattedDate }}. Caso esse prazo esteja expirado, será necessário solicitar outro código.</p>
    <br>
    <p>Atenciosamente,</p>

    <p>Equipe Excursionistas</p>

</body>

</html>
