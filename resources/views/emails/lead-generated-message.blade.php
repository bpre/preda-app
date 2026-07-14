<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $subject }}</title>
</head>
<body style="margin: 0; padding: 0; background: #ffffff; color: #111111; font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 1.5; text-align: left;">
    <div style="margin: 0; padding: 0;">
        {!! \Filament\Forms\Components\RichEditor\RichContentRenderer::make($body)->toHtml() !!}
    </div>
</body>
</html>
