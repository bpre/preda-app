<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $notification->subject ?? 'Powiadomienie o piśmie' }}</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; font-size: 13px; line-height: 1.7; color: #111111; text-align: left;">

        {!! $messageHtml !!}

        <div style="margin-top: 28px; line-height: 120%;">
            <div style="margin: 0;">Z wyrazami szacunku</div>
            <div style="height: 14px; line-height: 14px; font-size: 14px;">&nbsp;</div>
            <div style="margin: 0; font-weight: 700;">{{ $signatureName }}</div>

            @if (filled($signatureTitle))
                <div style="margin: 0;">{{ $signatureTitle }}</div>
            @endif

            @if(false)
            <div style="margin-top: 20px;">
                <a href="{{ $companyUrl }}" target="_blank" rel="noopener" style="display: inline-block; text-decoration: none;">
                    <img
                        src="{{ $logoUrl }}"
                        alt="{{ $companyName }}"
                        height="40"
                        style="display: block; height: 40px; width: auto; border: 0; outline: none; text-decoration: none;"
                    >
                </a>
            </div>
            @endif

        </div>
    </div>

</body>
</html>
