@extends('certificates.layout')

@section('styles')
<style>
    /* ============================================================
       CONVOCATION CERTIFICATE TEMPLATE
       L.B.S College of Engineering, Kasaragod
       Affiliated to APJ Abdul Kalam Technological University
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
        color: #1a1a2e;
    }

    .certificate-container {
        width: 100%;
        height: 100vh;
        position: relative;
        background: #fffdf5;
        overflow: hidden;
    }

    /* ---- OUTER DECORATIVE BORDER ---- */
    .outer-border {
        margin: 12px 14px;
        border: 3px solid #1a3c6e;
        padding: 5px;
        position: relative;
        min-height: calc(100vh - 24px);
        box-sizing: border-box;
    }
    .inner-border {
        border: 1.5px solid #b8860b;
        padding: 0;
        position: relative;
        min-height: calc(100vh - 38px);
        box-sizing: border-box;
    }

    /* ---- CORNER ORNAMENTS (CSS) ---- */
    .inner-border::before,
    .inner-border::after {
        content: '✦';
        position: absolute;
        font-size: 14px;
        color: #b8860b;
    }
    .inner-border::before {
        top: 6px;
        left: 10px;
    }
    .inner-border::after {
        top: 6px;
        right: 10px;
    }

    /* ---- HEADER / LOGO SECTION ---- */
    .header-section {
        padding: 22px 30px 8px 30px;
        text-align: center;
    }
    .header-section table {
        width: 100%;
        border-collapse: collapse;
    }
    .header-section td {
        vertical-align: middle;
    }

    .header-line {
        border: none;
        height: 1px;
        background: linear-gradient(to right, transparent, #1a3c6e, #b8860b, #1a3c6e, transparent);
        margin: 5px 40px;
    }

    /* ---- MAIN CERTIFICATE BODY ---- */
    .cert-body {
        padding: 8px 50px 15px 50px;
        text-align: center;
    }

    .convocation-title {
        font-family: 'Times New Roman', 'Georgia', serif;
        font-size: 46px;
        font-weight: bold;
        text-align: center;
        color: #1a3c6e;
        margin: 8px 0 0 0;
        letter-spacing: 5px;
        text-transform: uppercase;
    }

    .convocation-subtitle {
        font-family: 'Times New Roman', 'Georgia', serif;
        font-size: 20px;
        text-align: center;
        color: #b8860b;
        letter-spacing: 8px;
        text-transform: uppercase;
        margin-top: 2px;
        font-weight: bold;
    }

    .cert-preamble {
        font-size: 14px;
        color: #333;
        margin-top: 22px;
        line-height: 1.6;
        text-align: center;
    }

    .recipient-name-line {
        text-align: center;
        margin: 12px auto;
    }
    .recipient-name-line .name-underline {
        display: inline-block;
        min-width: 380px;
        border-bottom: 1.5px solid #1a3c6e;
        vertical-align: bottom;
        padding: 0 10px 2px 10px;
    }
    .recipient-name-line .name-value {
        font-family: 'Times New Roman', 'Georgia', serif;
        font-size: 28px;
        font-weight: bold;
        color: #6a1b6a;
        letter-spacing: 1px;
    }

    .degree-text {
        font-size: 15px;
        color: #333;
        margin-top: 12px;
        line-height: 1.7;
        text-align: center;
    }

    .degree-highlight {
        font-size: 20px;
        font-weight: bold;
        color: #1a3c6e;
        letter-spacing: 1px;
    }

    .branch-highlight {
        font-size: 16px;
        font-weight: bold;
        color: #333;
    }

    .convocation-details {
        font-size: 13px;
        color: #555;
        margin-top: 14px;
        line-height: 1.6;
        text-align: center;
        font-style: italic;
    }

    /* ---- DATE LINE ---- */
    .date-line {
        text-align: left;
        font-size: 12px;
        color: #333;
        padding: 10px 50px 0 50px;
        font-weight: bold;
    }

    /* ---- SIGNATURE SECTION ---- */
    .signatures-section {
        padding: 12px 40px 15px 40px;
    }
    .signatures-table {
        width: 100%;
        border-collapse: collapse;
    }
    .signatures-table td {
        vertical-align: bottom;
        padding: 0 10px;
    }
    .sig-block {
        text-align: center;
    }
    .sig-img-container {
        text-align: center;
        min-height: 30px;
    }
    .sig-line-border {
        border-top: 1px solid #1a3c6e;
        width: 70%;
        margin: 3px auto 0;
    }
    .sig-name {
        font-size: 12px;
        font-weight: bold;
        color: #1a3c6e;
        margin-top: 2px;
    }
    .sig-role {
        font-size: 11px;
        color: #333;
        margin-top: 1px;
        font-weight: bold;
    }

    /* ---- REGISTRATION NO ---- */
    .reg-number {
        font-size: 11px;
        color: #555;
        text-align: left;
        padding: 3px 50px;
    }
</style>
@endsection

@php
    /**
     * Helper: converts a logo file in public/images/certificates/
     * to a base64 data-URI that dompdf can render inline.
     */
    function convLogoBase64($filename) {
        $path = public_path("images/certificates/{$filename}");
        if (!is_readable($path)) return '';
        $data = file_get_contents($path);
        $ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = match($ext) {
            'jpg','jpeg' => 'image/jpeg',
            'png'        => 'image/png',
            'gif'        => 'image/gif',
            'svg'        => 'image/svg+xml',
            default      => 'image/png',
        };
        return "data:{$mime};base64," . base64_encode($data);
    }
@endphp

@section('content')
<div class="certificate-container">
    <div class="outer-border">
        <div class="inner-border">

            {{-- ============ HEADER SECTION ============ --}}
            <div class="header-section">
                <table>
                    <tr>
                        {{-- KTU Logo (Left, bigger) --}}
                        <td style="width: 20%; text-align: left; padding-left: 15px;">
                            <img src="{{ convLogoBase64('ktu_logo.png') }}" style="height: 100px;" alt="KTU">
                        </td>

                        {{-- LBS Logo (Center) --}}
                        <td style="width: 60%; text-align: center;">
                            <img src="{{ convLogoBase64('lbs_logo.png') }}" style="height: 80px;" alt="LBSCEK">
                        </td>

                        {{-- Empty right cell for balance --}}
                        <td style="width: 20%;">
                        </td>
                    </tr>
                </table>
            </div>

            <hr class="header-line">

            {{-- Registration Number --}}
            <div class="reg-number">
                Reg. No:
                @if(isset($certificate->metadata['reg_number']))
                    {{ $certificate->metadata['reg_number'] }}
                @else
                    {{ str_pad($certificate->id, 6, '0', STR_PAD_LEFT) }}
                @endif
            </div>

            {{-- ============ MAIN CERTIFICATE BODY ============ --}}
            <div class="cert-body">

                <div class="convocation-title">Convocation Certificate</div>
                <div class="convocation-subtitle">of degree</div>

                <div class="cert-preamble">
                    This is to certify that
                </div>

                <div class="recipient-name-line">
                    <span class="name-underline">
                        <span class="name-value">{{ $certificate->recipient_name }}</span>
                    </span>
                </div>

                <div class="degree-text">
                    has successfully completed the prescribed course of study and has passed the<br>
                    examination conducted by APJ Abdul Kalam Technological University and<br>
                    is hereby conferred the degree of
                </div>

                <div style="margin-top: 10px;">
                    <span class="degree-highlight">
                        @if($certificate->title)
                            {{ $certificate->title }}
                        @else
                            Bachelor of Technology
                        @endif
                    </span>
                </div>

                @if($certificate->description)
                <div style="margin-top: 6px;">
                    <span style="font-size: 14px; color: #555;">in</span>
                    <span class="branch-highlight">{{ $certificate->description }}</span>
                </div>
                @endif

                <div class="convocation-details">
                    at the Convocation held on
                    {{ \Carbon\Carbon::parse($certificate->issued_at)->format('jS F, Y') }}
                    at L.B.S College of Engineering, Kasaragod
                </div>

            </div>

            {{-- ============ DATE LINE ============ --}}
            <div class="date-line">
                Date: {{ \Carbon\Carbon::parse($certificate->issued_at)->format('d / m / Y') }}
            </div>

            {{-- ============ SIGNATURES ============ --}}
            <div class="signatures-section">
                <table class="signatures-table">
                    <tr>
                        {{-- Principal --}}
                        <td style="width: 33%; text-align: center;">
                            <div class="sig-block">
                                <div class="sig-img-container">
                                    <img src="{{ convLogoBase64('sig_2.png') }}" style="height: 32px;" alt="Principal Signature">
                                </div>
                                <div class="sig-line-border"></div>
                                <div class="sig-name">Principal</div>
                                <div class="sig-role">LBS College of Engineering</div>
                            </div>
                        </td>

                        {{-- Empty center space where seal used to be --}}
                        <td style="width: 34%;">
                        </td>

                        {{-- Vice Chancellor --}}
                        <td style="width: 33%; text-align: center;">
                            <div class="sig-block">
                                <div class="sig-img-container">
                                    <img src="{{ convLogoBase64('sig_5.png') }}" style="height: 32px;" alt="Vice Chancellor Signature">
                                </div>
                                <div class="sig-line-border"></div>
                                <div class="sig-name">Vice Chancellor</div>
                                <div class="sig-role">APJ Abdul Kalam Technological University</div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

        </div>
    </div>
</div>
@endsection
