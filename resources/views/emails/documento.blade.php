<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
</head>

<body style="font-family: Arial, sans-serif; margin: 0; padding: 0;">

    <div style="max-width: 600px; margin: 0 auto;">

        <div style="background: #003D7A; padding: 20px; text-align: center;">
            <h1 style="color: white; margin: 0;">Duoc UC</h1>
            <p style="color: #FFB800; margin: 5px 0 0;">Sede San Bernardo</p>
        </div>

        <div style="padding: 30px; background: #f5f5f5;">
            <h2 style="color: #003D7A;">Hola {{ $nombreAlumno }},</h2>
            <p>Tu <strong>{{ $tipoDocumento }}</strong> ha sido generado
                exitosamente desde el Tótem de Autoservicio.</p>
            <p>Encontrarás el documento adjunto en este correo en formato PDF.</p>
            <p style="color: #666; font-size: 12px; margin-top: 30px;">
                Fecha: {{ now()->format('d/m/Y H:i') }}
            </p>
        </div>

        <div style="background: #003D7A; padding: 10px; text-align: center;">
            <p style="color: white; font-size: 12px; margin: 0;">
                Tótem de Autoservicio — Duoc UC San Bernardo
            </p>
        </div>

    </div>

</body>

</html>
