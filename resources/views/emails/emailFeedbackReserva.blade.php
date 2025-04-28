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
            color: #111111;
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
            color: #111111;
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

        <div class="content" style="padding: 16px;">
            <p>
                <h2>Falta pouco para sua reserva ser confirmada! ⏳</h2>
            </p>

            <p style="color: #6A6A6A;">Olá, <strong>{{ $user->nome }}</strong>,</p>

            <p style="color: #6A6A6A;">
                Sua inscrição para a caravana <strong>{{ $caravana->titulo }}</strong> foi realizada! 🎉
            </p>

            <p style="color: #6A6A6A;">
                Para garantir sua vaga, basta efetuar o pagamento diretamente ao organizador.
                Fique à vontade para aguardar o contato ou enviar uma mensagem e garantir já sua reserva.
            </p>

            <p style="color: #6A6A6A;">
                <strong>Importante:</strong> Sua reserva só será confirmada após o pagamento ser realizado e aprovado
                pelo organizador.
                Assim que o pagamento for confirmado, você receberá um novo e-mail com as instruções completas sobre a
                caravana.
            </p>

            <div class="info">
                <p>Informações para contato com o organizador:</p>

                @if ($dadosOrganizador->nome_fantasia !== '')
                    <p><strong>👤 Organizador:</strong> <strong>{{ $dadosOrganizador->nome_fantasia }}</strong></p>
                @else
                    <p><strong>👤 Organizador:</strong> <strong>{{ $dadosOrganizador->razao_social }}</strong></p>
                @endif

                {{-- Formatação de telefone --}}
                @php
                    $telefone = preg_replace('/[^0-9]/', '', $dadosOrganizador->telefone_comercial);

                    if (strlen($telefone) === 11) {
                        $telefoneFormatado =
                            '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 5) . '-' . substr($telefone, 7);
                    } elseif (strlen($telefone) === 10) {
                        $telefoneFormatado =
                            '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 4) . '-' . substr($telefone, 6);
                    } else {
                        $telefoneFormatado = $dadosOrganizador->telefone_comercial;
                    }
                @endphp

                <p><strong>📞 Telefone:</strong>
                    <a href="https://api.whatsapp.com/send?phone={{ $telefone }}" target="_blank">
                        {{ $telefoneFormatado }}
                    </a>
                </p>

                <p><strong>📧 E-mail:</strong> {{ $dadosOrganizador->user->email }}</p>
            </div>

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
