@extends('certificates.layout')

@section('styles')
<style>
    /* ============================================================
       NSS Volunteer Certificate Template
       Matches official NSS/KTU Volunteer Certificate design
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
        color: #333;
    }

    .certificate-container {
        width: 100%;
        height: 100vh;
        position: relative;
        background: #ffffff;
        overflow: hidden;
    }

    /* ---- OUTER BORDER ---- */
    .certificate-border {
        margin: 15px 20px;
        border: 3px solid #C0392B;
        padding: 0;
        position: relative;
        min-height: calc(100vh - 30px);
        box-sizing: border-box;
    }

    /* ---- HEADER SECTION ---- */
    .header-section {
        padding: 20px 30px 10px 30px;
        text-align: center;
        position: relative;
    }
    .header-section table {
        width: 100%;
        border-collapse: collapse;
    }
    .header-section td {
        vertical-align: middle;
    }

    .govt-text {
        font-family: 'Times New Roman', serif;
        font-size: 13px;
        font-weight: bold;
        color: #C0392B;
        letter-spacing: 3px;
        text-transform: uppercase;
        margin-bottom: 2px;
    }

    .nss-title {
        font-family: 'Times New Roman', serif;
        font-size: 38px;
        font-weight: bold;
        color: #C0392B;
        letter-spacing: 2px;
        line-height: 1.1;
        margin: 0;
    }

    .university-name {
        font-family: 'Times New Roman', serif;
        font-size: 16px;
        font-weight: bold;
        color: #333;
        letter-spacing: 2px;
        text-transform: uppercase;
        margin-top: 2px;
    }

    /* ---- HORIZONTAL RULE ---- */
    .header-divider {
        border: none;
        border-top: 2px solid #C0392B;
        margin: 8px 25px 5px 25px;
    }

    /* ---- ENROLLMENT NUMBER ---- */
    .enrollment-number {
        text-align: left;
        font-size: 12px;
        font-weight: bold;
        color: #333;
        padding: 0 30px;
        margin-bottom: 10px;
    }

    /* ---- MAIN CERTIFICATE BODY ---- */
    .cert-body {
        padding: 10px 50px 20px 50px;
        text-align: center;
    }

    .cert-title {
        font-family: 'Times New Roman', 'Georgia', serif;
        font-size: 52px;
        font-weight: bold;
        font-style: italic;
        text-align: center;
        color: #111;
        margin: 10px 0 15px 0;
        letter-spacing: 2px;
    }

    .award-text {
        font-size: 16px;
        color: #555;
        margin: 15px 0 5px 0;
    }

    .name-line {
        text-align: center;
        margin: 10px auto;
    }
    .name-line .dashes {
        display: inline-block;
        width: 450px;
        border-bottom: 1.5px dashed #555;
        vertical-align: bottom;
        margin: 0 3px;
        position: relative;
    }
    .name-line .name-value {
        font-size: 22px;
        font-weight: bold;
        color: #111;
        position: relative;
        top: 5px;
    }

    .of-line {
        text-align: center;
        margin: 15px auto;
        font-size: 15px;
        color: #555;
    }
    .of-line .dashes {
        display: inline-block;
        width: 520px;
        border-bottom: 1.5px dashed #555;
        vertical-align: bottom;
        margin: 0 3px;
        position: relative;
    }
    .of-line .of-value {
        font-size: 15px;
        color: #333;
        position: relative;
        top: 5px;
    }

    .description-text {
        font-size: 14px;
        color: #555;
        line-height: 1.7;
        margin: 15px auto;
        max-width: 85%;
        text-align: center;
    }

    /* ---- SIGNATURE SECTION ---- */
    .signatures-section {
        padding: 25px 40px 20px 40px;
    }
    .signatures-table {
        width: 100%;
        border-collapse: collapse;
    }
    .signatures-table td {
        vertical-align: bottom;
        padding: 0 10px;
    }
    .sig-left {
        text-align: left;
        width: 20%;
    }
    .sig-center {
        text-align: center;
        width: 40%;
    }
    .sig-right {
        text-align: center;
        width: 40%;
    }

    .sig-label {
        font-size: 13px;
        font-weight: bold;
        color: #333;
        margin-bottom: 3px;
    }
    .sig-name {
        font-size: 13px;
        font-weight: bold;
        color: #111;
        margin-top: 3px;
    }
    .sig-role {
        font-size: 12px;
        font-weight: bold;
        color: #333;
        margin-top: 1px;
    }
</style>
@endsection

@php
    /**
     * Helper: converts a logo file in public/images/certificates/
     * to a base64 data-URI that dompdf can render inline.
     */
    function nssLogoBase64($filename) {
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
    <div class="certificate-border">

        {{-- ============ HEADER SECTION ============ --}}
        <div class="header-section">
            <table>
                <tr>
                    {{-- KTU Logo --}}
                    <td style="width: 12%; text-align: left;">
                        <img src="{{ nssLogoBase64('ktu_logo.png') }}" style="height: 70px;" alt="KTU">
                    </td>

                    {{-- Central Text --}}
                    <td style="width: 76%; text-align: center;">
                        {{-- Kerala Emblem --}}
                        <div style="margin-bottom: 3px;">
                            <img src="{{ nssLogoBase64('kerala_emblem.png') }}" style="height: 40px;" alt="Kerala Emblem">
                        </div>
                        <div class="govt-text">Government of Kerala</div>
                        <div class="nss-title">NATIONAL SERVICE SCHEME</div>
                        <div class="university-name">APJ Abdul Kalam Technological University</div>
                    </td>

                    {{-- NSS Logo --}}
                    <td style="width: 12%; text-align: right; padding-right: 15px;">
                        <img src="{{ nssLogoBase64('nss_logo.png') }}" style="height: 70px;" alt="NSS">
                    </td>
                </tr>
            </table>
        </div>

        {{-- Horizontal divider --}}
        <hr class="header-divider">

        {{-- Enrollment Number --}}
        <div class="enrollment-number">
            Enrollment Number :
            @if(isset($certificate->metadata['enrollment_number']))
                {{ $certificate->metadata['enrollment_number'] }}
            @endif
        </div>

        {{-- ============ MAIN CERTIFICATE BODY ============ --}}
        <div class="cert-body">

            <div class="cert-title">VOLUNTEER CERTIFICATE</div>

            <div class="award-text">
                This Certificate of Merit is awarded to Mr/Miss
            </div>

            <div class="name-line">
                <span class="dashes">
                    <span class="name-value">{{ $certificate->recipient_name }}</span>
                </span>
            </div>

            <div class="of-line">
                of <span class="dashes">
                    <span class="of-value">
                        @if($certificate->description)
                            {{ $certificate->description }}
                        @endif
                    </span>
                </span>
            </div>

            <div class="description-text">
                for successfully completing two years of National Service Scheme (NSS) volunteership with sufficient
                hours of regular activities and special camping programme and for the excellent and dedicated services
                provided to the society as a NSS volunteer
            </div>

        </div>

        {{-- ============ SIGNATURES ============ --}}
        <div class="signatures-section">
            <table class="signatures-table">
                <tr>
                    <td class="sig-left">
                        <div class="sig-label">Date :
                            @if($certificate->issued_at)
                                {{ $certificate->issued_at->format('d M Y') }}
                            @endif
                        </div>
                        <div class="sig-label" style="margin-top: 5px;">Place : LBS College of Engineering</div>
                    </td>
                    <td class="sig-center">
                        <div style="text-align: center;">
                            <img src="{{ nssLogoBase64('sig_3.png') }}" style="height: 30px;" alt="Signature">
                        </div>
                        <div style="border-top: 1px solid #888; width: 60%; margin: 3px auto 0;"></div>
                        <div class="sig-name">Joy Varghese V M</div>
                        <div class="sig-role">Programme Coordinator</div>
                    </td>
                    <td class="sig-right">
                        <div style="text-align: center;">
                            <img src="{{ nssLogoBase64('sig_5.png') }}" style="height: 30px;" alt="Signature">
                        </div>
                        <div style="border-top: 1px solid #888; width: 60%; margin: 3px auto 0;"></div>
                        <div class="sig-name">Dr Saji Gopinath</div>
                        <div class="sig-role">Vice Chancellor</div>
                    </td>
                </tr>
            </table>
        </div>

    </div>
</div>
@endsection
