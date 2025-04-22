<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Recuperação de Senha - Excursionistas</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
            color: #333333;
        }

        .container {
            max-width: 600px;
            margin: auto;
            background-color: #fff;
            border: 1px solid #eaeaea;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .header {
            background-color: #FF6D3C;
            padding: 30px 20px;
            text-align: center;
            color: white;
        }

        .header img {
            max-width: 100px;
            margin-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .content {
            padding: 30px 20px;
        }

        .content p {
            font-size: 16px;
            line-height: 1.6;
            margin: 10px 0;
        }

        .code {
            background-color: #FFAA8E;
            color: #000;
            font-size: 24px;
            font-weight: bold;
            padding: 15px;
            text-align: center;
            margin: 30px 0;
            border-radius: 6px;
            letter-spacing: 4px;
        }

        .footer {
            background-color: #f4f4f4;
            text-align: center;
            padding: 15px;
            font-size: 14px;
            color: #888888;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <img src="https://suricatodev.s3.sa-east-1.amazonaws.com/assets/logo.png" alt="Logo" />
            <h1>Excursionistas</h1>
        </div>
        <div class="content">
            <p>Olá, <strong>{{ $user->nome }}</strong>,</p>

            <p>Você solicitou a recuperação da sua senha no sistema <strong>Excursionistas</strong>.</p>

            <p>Use o código abaixo para prosseguir com a redefinição de senha:</p>

            <div class="code">{{ $code }}</div>

            <p>⚠️ Este código é válido até as <strong>{{ $formattedTime }}</strong> do dia
                <strong>{{ $formattedDate }}</strong>.</p>

            <p>Se você não solicitou esta recuperação, por favor, ignore este e-mail.</p>

            <p>Atenciosamente,<br>Equipe Excursionistas</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} SuricatoDev | Todos os direitos reservados.
        </div>
    </div>
</body>

</html>
