<!-- resources/views/emails/notifyUser.blade.php -->
<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .header {
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
            color: #3d4852;
            font-size: 19px;
            font-weight: bold;
            text-decoration: none;
            display: center;
            text-align: center;
        }

        .content {
            padding: 20px;
            display: block;
            justify-content: center;

        }

        .hr {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
            color: #3d4852;
            font-size: 19px;
            font-weight: bold;
            text-decoration: none;
        }

        a.button {
            padding: 2px 24px;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
            border-radius: 4px;
            color: #fff;
            display: inline-block;
            overflow: hidden;
            text-decoration: none;
            background-color: #48bb78;
            border-bottom: 8px solid #48bb78;
            border-left: 18px solid #48bb78;
            border-right: 18px solid #48bb78;
            border-top: 8px solid #48bb78;
        }

        .flex-center {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .footer {
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
            font-size: 16px;
            line-height: 1.5em;
            margin-top: 0;
            text-align: left;
        }

        p {
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
            font-size: 16px;
            line-height: 1.5em;
            margin-top: 0;
            text-align: left;
        }

        .link {
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
            line-height: 1.5em;
            margin-top: 0;
            text-align: left;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>VAC</h1>
    </div>
    <div class="content">
        <p>¡Hola!</p>
        <p>Días solicitados: {!! $days_string !!}</p>
        <p>{{ $confirmation_message }}</p>
        <div class="flex-center">
            <a href="{{ $link }}" class="button">Entrar</a>
        </div>
        <footer class="footer">
            <p class="footer">Atentamente,<br>
                VAC.</p>
        </footer>
        <hr class="hr" />
    </div>
    <div class="content">
        <p class="link">
            Si estás teniendo problemas con la verificación a través del botón "Verificar correo", copia y pega el
            siguiente enlace en tu navegador: <a href="{{ $link }}">{{ $link }}</a>
        </p>
    </div>
</body>

</html>
