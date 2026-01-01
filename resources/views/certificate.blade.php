<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificate</title>
    <style>
        @page {
            margin: 0;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: 'Georgia', serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #333;
        }
        .certificate-container {
            width: 100%;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            box-sizing: border-box;
        }
        .certificate {
            background: white;
            padding: 60px 80px;
            border: 20px solid #f0f0f0;
            box-shadow: 0 0 40px rgba(0,0,0,0.2);
            text-align: center;
            max-width: 800px;
            position: relative;
        }
        .certificate::before {
            content: '';
            position: absolute;
            top: 30px;
            left: 30px;
            right: 30px;
            bottom: 30px;
            border: 2px solid #667eea;
            pointer-events: none;
        }
        .certificate-header {
            margin-bottom: 30px;
        }
        .certificate-title {
            font-size: 48px;
            font-weight: bold;
            color: #667eea;
            margin: 0 0 10px 0;
            text-transform: uppercase;
            letter-spacing: 3px;
        }
        .certificate-subtitle {
            font-size: 18px;
            color: #666;
            margin: 0;
        }
        .certificate-body {
            margin: 40px 0;
        }
        .recipient-name {
            font-size: 36px;
            font-weight: bold;
            color: #333;
            margin: 20px 0;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
            display: inline-block;
        }
        .certificate-text {
            font-size: 16px;
            line-height: 1.8;
            color: #555;
            margin: 20px 0;
        }
        .certificate-description {
            font-size: 14px;
            color: #777;
            font-style: italic;
            margin: 20px 0;
        }
        .certificate-footer {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .signature-block {
            text-align: center;
            flex: 1;
        }
        .signature-line {
            border-top: 2px solid #333;
            width: 200px;
            margin: 0 auto 10px;
        }
        .signature-label {
            font-size: 12px;
            color: #666;
        }
        .certificate-date {
            font-size: 14px;
            color: #666;
            text-align: center;
            margin-top: 30px;
        }
        .certificate-id {
            font-size: 10px;
            color: #999;
            text-align: center;
            margin-top: 10px;
            font-family: monospace;
        }
        .seal {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 12px;
            margin: 0 auto 10px;
            text-align: center;
            line-height: 1.2;
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <div class="certificate">
            <div class="certificate-header">
                <h1 class="certificate-title">Certificate</h1>
                <p class="certificate-subtitle">of Achievement</p>
            </div>
            
            <div class="certificate-body">
                <p class="certificate-text">This is to certify that</p>
                <div class="recipient-name">{{ $certificate->recipient_name }}</div>
                <p class="certificate-text">has successfully completed</p>
                <h2 style="font-size: 24px; color: #667eea; margin: 20px 0;">{{ $certificate->title }}</h2>
                
                @if($certificate->description)
                <p class="certificate-description">{{ $certificate->description }}</p>
                @endif
            </div>
            
            <div class="certificate-footer">
                <div class="signature-block">
                    <div class="seal">
                        <span>VERIFIED</span>
                    </div>
                    <div class="signature-line"></div>
                    <p class="signature-label">Authorized Signature</p>
                </div>
            </div>
            
            <div class="certificate-date">
                Issued on {{ \Carbon\Carbon::parse($certificate->issued_at)->format('F d, Y') }}
            </div>
            
            @if($certificate->ipfs_cid)
            <div class="certificate-id">
                Certificate ID: {{ $certificate->ipfs_cid }}
            </div>
            @endif
        </div>
    </div>
</body>
</html>
