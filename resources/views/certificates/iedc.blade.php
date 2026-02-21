@extends('certificates.layout')

@section('styles')
<style>
    /* ============================================================
       IEDC Certificate Template
       Matches official IEDC/IIC Certificate of Participation
       Uses real institutional logos (base64 embedded for dompdf)
       ============================================================ */
    @page {
        margin: 0;
        size: A4 landscape;
    }
    body {
        margin: 0;
        padding: 0;
        font-family: 'Times New Roman', 'Georgia', serif;
        background: #fff;
        color: #222;
    }

    .certificate-container {
        width: 100%;
        height: 100vh;
        position: relative;
        background: #ffffff;
        overflow: hidden;
    }

    /* ---- TOP LOGO BAR ---- */
    .logo-bar {
        width: 100%;
        padding: 18px 40px 10px 30px;
        box-sizing: border-box;
    }
    .logo-bar table {
        width: 100%;
        border-collapse: collapse;
    }
    .logo-bar td {
        vertical-align: middle;
        text-align: center;
        padding: 0 6px;
    }
    .logo-bar img {
        display: inline-block;
        vertical-align: middle;
    }
    .logo-label {
        font-family: Arial, sans-serif;
        font-size: 7px;
        color: #333;
        font-weight: bold;
        line-height: 1.2;
        display: inline-block;
        vertical-align: middle;
        text-align: left;
        margin-left: 4px;
    }

    /* ---- MAIN CERTIFICATE BODY ---- */
    .cert-main {
        margin: 5px 30px 20px 30px;
        border: 3px solid #1565C0;
        outline: 1px solid #1565C0;
        outline-offset: 4px;
        padding: 30px 40px 25px 40px;
        position: relative;
        min-height: 380px;
    }

    .cert-title {
        font-family: 'Times New Roman', 'Georgia', serif;
        font-size: 52px;
        font-weight: bold;
        font-style: italic;
        text-align: center;
        color: #111;
        margin-top: 15px;
        letter-spacing: 3px;
    }
    .cert-subtitle {
        font-family: 'Times New Roman', 'Georgia', serif;
        font-size: 22px;
        font-weight: bold;
        text-align: center;
        color: #333;
        letter-spacing: 5px;
        text-transform: uppercase;
        margin-top: 2px;
    }

    .presented-to {
        text-align: center;
        font-size: 14px;
        color: #444;
        text-transform: uppercase;
        letter-spacing: 3px;
        margin-top: 30px;
    }

    .participant-name {
        text-align: center;
        font-family: 'Times New Roman', 'Georgia', serif;
        font-size: 36px;
        font-weight: bold;
        color: #111;
        margin-top: 10px;
        letter-spacing: 1px;
    }
    .participant-name .chevrons {
        color: #555;
        font-weight: normal;
    }

    .recognition-text {
        text-align: center;
        font-size: 13px;
        color: #444;
        margin-top: 15px;
        font-style: italic;
    }
    .recognition-text .blank-line {
        display: inline-block;
        width: 180px;
        border-bottom: 1px solid #333;
        vertical-align: bottom;
        margin: 0 3px;
    }

    .event-details {
        text-align: center;
        font-size: 13px;
        color: #444;
        margin-top: 8px;
    }
    .event-details .blank-line {
        display: inline-block;
        width: 160px;
        border-bottom: 1px solid #333;
        vertical-align: bottom;
        margin: 0 3px;
    }

    /* ---- SIGNATURE SECTION ---- */
    .signatures-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 45px;
    }
    .signatures-table td {
        width: 33.33%;
        text-align: center;
        vertical-align: bottom;
        padding: 0 15px;
    }
    .sig-line {
        font-family: 'Brush Script MT', 'Segoe Script', cursive;
        font-size: 16px;
        color: #333;
        margin-bottom: 3px;
    }
    .sig-name {
        font-size: 12px;
        font-weight: bold;
        color: #0D47A1;
        margin-top: 0;
    }
    .sig-role {
        font-size: 11px;
        color: #0D47A1;
        margin-top: 1px;
    }
</style>
@endsection

@php
    // Helper to convert image files to base64 data URIs for dompdf compatibility
    function logoBase64($filename) {
        $path = public_path('images/certificates/' . $filename);
        if (!file_exists($path)) return '';
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $mime = ($ext === 'jpg' || $ext === 'jpeg') ? 'image/jpeg' : 'image/png';
        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
    }
@endphp

@section('content')
<div class="certificate-container">

    {{-- ============ TOP LOGO BAR ============ --}}
    <div class="logo-bar">
        <table>
            <tr>
                {{-- LBS College of Engineering --}}
                <td style="width: 24%; text-align: left;">
                    <img src="{{ logoBase64('lbs_logo.png') }}" style="height: 50px;" alt="LBS COE">
                </td>

                {{-- Ministry of Education / Ashoka Emblem --}}
                <td style="width: 16%;">
                    <img src="{{ logoBase64('ashoka_emblem.png') }}" style="height: 48px;" alt="GOI">
                    <span class="logo-label">
                        Ministry of Education<br>Government of India
                    </span>
                </td>

                {{-- AICTE --}}
                <td style="width: 10%;">
                    <img src="{{ logoBase64('aicte_logo.png') }}" style="height: 50px;" alt="AICTE">
                </td>

                {{-- MoE's Innovation Cell --}}
                <td style="width: 20%;">
                    <img src="{{ logoBase64('mic_logo.png') }}" style="height: 50px;" alt="MoE Innovation Cell">
                </td>

                {{-- IIC - Institution's Innovation Council --}}
                <td style="width: 16%;">
                    <img src="{{ logoBase64('iic_logo.png') }}" style="height: 45px;" alt="IIC">
                </td>

                {{-- IEDC Logo --}}
                <td style="width: 14%; padding-right: 15px;">
                    <img src="{{ logoBase64('iedc_logo.jpg') }}" style="height: 45px;" alt="IEDC">
                </td>
            </tr>
        </table>
    </div>

    {{-- ============ MAIN CERTIFICATE BODY ============ --}}
    <div class="cert-main">

        <div class="cert-title">CERTIFICATE</div>
        <div class="cert-subtitle">OF PARTICIPATION</div>

        <div class="presented-to">
            This certificate is proudly presented to
        </div>

        <div class="participant-name">
            {{ $certificate->recipient_name }}
        </div>

        <div class="recognition-text">
            in recognition of participation in the
            <span class="blank-line">
                @if($certificate->title)
                    <span style="font-style: normal; font-weight: bold; font-size: 14px; position: relative; top: -2px;">{{ $certificate->title }}</span>
                @endif
            </span>
        </div>

        <div class="event-details">
            <span class="blank-line">
                @if($certificate->description)
                    <span style="font-style: normal; font-size: 12px; position: relative; top: -2px;">{{ $certificate->description }}</span>
                @endif
            </span>
            &nbsp; at &nbsp;
            <span class="blank-line">
                <span style="font-style: normal; font-size: 12px; position: relative; top: -2px;">LBS College of Engineering</span>
            </span>
            &nbsp; held on &nbsp;
            <span class="blank-line">
                <span style="font-style: normal; font-size: 12px; position: relative; top: -2px;">
                    {{ \Carbon\Carbon::parse($certificate->issued_at)->format('d M Y') }}
                </span>
            </span>
        </div>

        {{-- ============ SIGNATURES ============ --}}
        <table class="signatures-table">
            <tr>
                <td>
                    <div style="text-align: center;"><img src="{{ logoBase64('sig_2.png') }}" style="height: 35px;" alt="Dr. Vinodu George Signature"></div>
                    <div style="border-top: 1px solid #888; width: 70%; margin: 3px auto 0;"></div>
                    <div class="sig-name">Dr. Vinodu George</div>
                    <div class="sig-role">IIC President</div>
                </td>
                <td>
                    <div style="text-align: center;"><img src="{{ logoBase64('sig_4.png') }}" style="height: 35px;" alt="Dr. Sarith Divakar Signature"></div>
                    <div style="border-top: 1px solid #888; width: 70%; margin: 3px auto 0;"></div>
                    <div class="sig-name">Dr. Sarith Divakar M</div>
                    <div class="sig-role">Nodal Officer</div>
                </td>
                <td>
                    <div style="text-align: center;"><img src="{{ logoBase64('sig_1.png') }}" style="height: 35px;" alt="Sanjay K P Signature"></div>
                    <div style="border-top: 1px solid #888; width: 70%; margin: 3px auto 0;"></div>
                    <div class="sig-name">Sanjay K P</div>
                    <div class="sig-role">CEO, IEDC LBSCEK</div>
                </td>
            </tr>
        </table>

    </div>

</div>
@endsection
