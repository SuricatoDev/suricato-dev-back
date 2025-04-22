<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Pedido de Suporte - Excursionistas</title>
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
            <p>Olá,
                @if ($dadosOrganizador->nome_fantasia !== '')
                    <strong>{{ $dadosOrganizador->nome_fantasia }}</strong>,
            </p>
        @else
            <strong>{{ $dadosOrganizador->razao_social }}</strong>,</p>
            @endif

            <p>Um passageiro solicitou reserva em uma de suas caravanas!</p>

            <div class="info">
                <p><strong>📌 Caravana:</strong> {{ $caravana->titulo }}</p>

                <p>Entre em contato com o passageiro para formalizar a reserva.</p>
                {{-- Informações de contato com o passageiro --}}
                <p><strong>👤 Passageiro:</strong> {{ $user->nome }}</p>
                <p><strong>📞 Telefone:</strong>
                    <a href="https://api.whatsapp.com/send?phone={{ preg_replace('/[^0-9]/', '', $telefonePassageiro) }}" target="_blank">
                        {{ $telefonePassageiro }}
                    </a>
                </p>
                <p><strong>📧 E-mail:</strong> {{ $user->email }}</p>
            </div>
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
