
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Confirmação de Cadastro - Excursionistas</title>
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
            max-width: 128px;
            max-height: 128px;
            margin-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            color: white;
            text-align: center;
            font-weight: bold;
            /* Caso queira deixar mais destacado */
        }

        .content {
            padding: 30px 20px;
        }

        .content p {
            font-size: 16px;
            line-height: 1.6;
            margin: 10px 0;
        }

        .support-box {
            background-color: #FFAA8E;
            color: #000;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }

        .support-box h3 {
            margin-top: 0;
            font-size: 20px;
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

        .info {
            margin-top: 20px;
            font-size: 14px;
            color: #555;
        }

        .footer {
            background-color: #f4f4f4;
            text-align: center;
            font-size: 14px;
            color: #888888;
            padding: 20px;
            line-height: 1.6;
            width: 100%;
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
        <div class="content">
            <p>Olá, <strong>{{ $user->nome }}</strong>,</p>

            <p>Obrigado por se juntar a nós no <strong>Excursionistas</strong>!</p>

            <p>Confirme seu e-mail para ter acesso a todas as funcionalidades do site.</p>

            <div class="code">
                <a href="{{ $link }}">Confirmar e-mail</a>
            </div>

            <p>Se você não se cadastrou no site, por favor, ignore este e-mail.</p>
            <br>
            <p>Atenciosamente,<br>Equipe Excursionistas</p>
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
