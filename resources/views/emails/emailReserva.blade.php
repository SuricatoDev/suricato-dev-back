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
            /* Isso deve garantir que o conteúdo seja centralizado */
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 200px;
            /* Ajuste conforme necessário */
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
            width: 100%;
            position: relative;
            bottom: 0;
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
            <p>Olá,
                @if($dadosOrganizador->nome_fantasia !== '')
                    <strong>{{$dadosOrganizador->nome_fantasia}}</strong>,</p>
                @else
                    <strong>{{$dadosOrganizador->razao_social}}</strong>,</p>
                @endif

            <p>Um passageiro solicitou reserva em uma de suas caravanas!</p>

            <div class="info">
                <p><strong>📌 Caravana:</strong> {{ $caravana->titulo }}</p>

                <p>Entre em contato com o passageiro para formalizar a reserva.</p>
                {{-- Informações de contato com o passageiro --}}
                <p><strong>👤 Passageiro:</strong> {{ $user->nome }}</p>
                <p><strong>📞 Telefone:</strong> {{ $user->telefone }}</p>
                <p><strong>📧 E-mail:</strong> {{ $user->email }}</p>
            </div>

            <p>Atenciosamente,<br>Sistema Excursionistas</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} SuricatoDev | Todos os direitos reservados.
        </div>
    </div>
</body>

</html>
