<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; margin: 0; padding: 0;">

    <div style="max-width: 600px; margin: 0 auto;">

        <div style="background: #045174; padding: 20px; text-align: center; border-bottom: 4px solid #e8a020;">
            <h1 style="color: white; margin: 0;">Duoc UC</h1>
            <p style="color: #e8a020; margin: 5px 0 0;">Sede San Bernardo</p>
        </div>

        <div style="padding: 30px; background: #f7f7f7; text-align: center;">
            <h2 style="color: #045174;">Hola {{ $ticket->nombre }},</h2>
            <p>Tu ticket de atención ha sido generado exitosamente.</p>

            <div style="background: #045174; color: white; padding: 20px;
                        border-radius: 15px; margin: 20px auto; max-width: 300px;">
                <p style="margin: 0; font-size: 14px; color: #e8a020;">
                    Tu número de ticket
                </p>
                <h1 style="margin: 10px 0; font-size: 48px; letter-spacing: 5px;">
                    {{ $ticket->ticket_numero }}
                </h1>
                <p style="margin: 0; font-size: 14px;">
                    Área: {{ $ticket->motivo }}
                </p>
            </div>

            <p style="font-size: 14px; color: #4f5b66;">
                Por favor acércate al mesón cuando tu número aparezca en pantalla.
            </p>
            <p style="color: #4f5b66; font-size: 12px; margin-top: 20px;">
                Fecha: {{ now()->format('d/m/Y H:i') }}
            </p>
        </div>

        <div style="background: #045174; padding: 10px; text-align: center;">
            <p style="color: white; font-size: 12px; margin: 0;">
                Tótem de Autoservicio — Duoc UC San Bernardo
            </p>
        </div>

    </div>

</body>
</html>
