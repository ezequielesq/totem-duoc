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

        <div style="padding: 30px; background: #f5f5f5; text-align: center;">
            <h2 style="color: #003D7A;">Hola {{ $ticket->nombre }},</h2>
            <p>Tu ticket de atención ha sido generado exitosamente.</p>

            <div style="background: #003D7A; color: white; padding: 20px;
                        border-radius: 15px; margin: 20px auto; max-width: 300px;">
                <p style="margin: 0; font-size: 14px; color: #FFB800;">
                    Tu número de ticket
                </p>
                <h1 style="margin: 10px 0; font-size: 48px; letter-spacing: 5px;">
                    {{ $ticket->ticket_numero }}
                </h1>
                <p style="margin: 0; font-size: 14px;">
                    Área: {{ $ticket->motivo }}
                </p>
            </div>

            <p style="font-size: 14px; color: #666;">
                Por favor acércate al mesón cuando tu número aparezca en pantalla.
            </p>
            <p style="color: #666; font-size: 12px; margin-top: 20px;">
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
