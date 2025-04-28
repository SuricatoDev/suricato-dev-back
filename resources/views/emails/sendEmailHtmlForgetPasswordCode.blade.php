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
            color: #222222;
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

        .content h2 {
            font-size: 20px;
            color: #FF6D3C;
            margin-bottom: 20px;
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
        <div class="header" style="background-color: #FF6D3C; padding: 30px 20px; text-align: center; color: white;">
            <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                <tr>
                    <td align="center">
                        <img src="https://suricatodev.s3.sa-east-1.amazonaws.com/assets/logo.png" alt="Logo"
                            width="128" style="display: block; margin: 0 auto 10px;" />
                    </td>
                </tr>
                <tr>
                    <td align="center">
                        <h1 style="margin: 0; font-size: 24px;">Excursionistas</h1>
                    </td>
                </tr>
            </table>
        </div>
        <div class="content" style="padding: 16px;">
            <p>
                <h2>Solicitação de recuperação de senha. 🔑</h2>
            </p>

            <p style="color: #6A6A6A;">Olá, <strong>{{ $user->nome }}</strong>,</p>

            <p style="color: #6A6A6A;">Você solicitou a recuperação da sua senha no sistema <strong>Excursionistas</strong>.</p>

            <p style="color: #6A6A6A;">Use o código abaixo para prosseguir com a redefinição de senha:</p>
            <p style="color: #6A6A6A;">Após este prazo, será necessário solicitar um novo código.</p>

            <div class="code">{{ $code }}</div>

            <p style="color: #6A6A6A;">⚠️ Este código é válido até as <strong>{{ $formattedTime }}</strong> do dia
                <strong>{{ $formattedDate }}</strong>.</p>

            <p style="color: #6A6A6A;">Se você não solicitou esta recuperação, por favor, ignore este e-mail.</p>
            <br>
            <p style="color: #6A6A6A;">Atenciosamente,<br>Equipe Excursionistas</p>
        </div>
        <table role="presentation" style="width: 100%; background-color: #f4f4f4; padding: 15px;">
            <tr>
                <td style="text-align: center; font-size: 14px; color: #888888; line-height: 1.6;">
                    &copy; {{ date('Y') }} SuricatoDev | Todos os direitos reservados.
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
