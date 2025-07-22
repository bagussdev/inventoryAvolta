<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Akun - Support Portal</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 30px auto;
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.08);
        }

        h2 {
            color: #4F46E5;
            font-size: 22px;
            margin-bottom: 20px;
        }

        p {
            font-size: 15px;
            color: #333333;
            margin: 10px 0;
        }

        a {
            color: #4F46E5;
            text-decoration: none;
        }

        .info-box {
            background-color: #f1f1f1;
            padding: 15px;
            border-radius: 6px;
            margin-top: 15px;
            font-size: 14px;
        }

        .footer {
            font-size: 12px;
            color: #999999;
            margin-top: 30px;
            text-align: center;
        }

        @media (max-width: 600px) {
            .container {
                padding: 20px;
                margin: 10px;
            }

            h2 {
                font-size: 20px;
            }

            p,
            .info-box {
                font-size: 14px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Selamat Datang di Support Portal Avolta Bali</h2>
        <p>Halo <strong>{{ $user->name }}</strong>,</p>
        <p>Akun Anda telah berhasil didaftarkan di <a href="https://support-portal.net">support-portal.net</a>.</p>

        <div class="info-box">
            <p><strong>🔐 Username:</strong> {{ $user->email }}</p>
            <p><strong>🔑 Password:</strong> {{ $plainPassword }}</p>
        </div>

        <p>Silakan login dan segera ubah password Anda untuk keamanan akun.</p>
        <p>Jika Anda tidak merasa mendaftar, abaikan email ini.</p>

        <div class="footer">
            &copy; {{ date('Y') }} Support Portal. All rights reserved.
        </div>
    </div>
</body>

</html>
