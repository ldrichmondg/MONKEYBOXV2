<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Recuperar contraseña - Monkey Box</title>
<style>
  body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
  }
  .container {
    max-width: 600px;
    margin: 0 auto;
    background: #ffffff;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
  }
  h1 {
    color: #333333;
    font-size: 24px;
    text-align: center;
  }
  p {
    color: #333333;
    font-size: 16px;
    line-height: 1.5;
  }
  .btn {
    display: inline-block;
    margin-top: 20px;
    padding: 12px 24px;
    background: #0066cc;
    color: #ffffff!important;
    text-decoration: none;
    border-radius: 4px;
    font-weight: bold;
    text-align: center;
    background-color:#F89434;
  }
  .footer {
    margin-top: 30px;
    text-align: center;
    font-size: 12px;
    color: #777777;
  }
  .footer a {
    color: #777777;
    text-decoration: none;
  }
</style>
</head>
<body>
  <table width="100%" border="0" cellspacing="0" cellpadding="0" style="padding:20px 0;">
    <tr>
      <td>
        <div class="container">

            <div style="text-align: center;">
                <img src="{{ $message->embed(public_path('/images/LogoMBSidebar.png')) }}"
                    alt="Logo" style="display: inline-block;">
            </div>

          <h1>Recuperación de Contraseña</h1>
          <p>Hola,</p>
          <p>
            Hemos recibido una solicitud para restablecer tu contraseña en <strong>Monkey Box</strong>.
            Si no has solicitado este cambio, simplemente ignora este correo.
          </p>
          <p>
            Para continuar con el proceso de recuperación, haz clic en el siguiente botón:
          </p>
          <p style="text-align:center;">
            <a href="{{ $url }}"  class="btn">Restablecer Contraseña</a>
          </p>
          <p>
            Este enlace es válido durante las próximas 24 horas. Si expiró, deberás solicitar nuevamente la recuperación de tu contraseña.
          </p>
          <div class="footer">
            <p>&copy; 2025 Monkey Box</p>

          </div>
        </div>
      </td>
    </tr>
  </table>
</body>
</html>

