@extends('certificates.layout')

@section('styles')
<style>
    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    }
    .default-border {
        border: 20px solid #2c3e50;
        height: 100%;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 40px;
    }
    .header-text {
        font-family: 'Times New Roman', serif;
        font-size: 60px;
        color: #2c3e50;
        margin-bottom: 20px;
    }
    .sub-header {
        font-size: 24px;
        color: #7f8c8d;
        margin-bottom: 40px;
        letter-spacing: 2px;
    }
    .recipient-name {
        font-family: 'Pinyon Script', cursive; /* Fallback will be used if font not loaded */
        font-size: 50px;
        color: #e74c3c;
        border-bottom: 2px solid #bdc3c7;
        padding-bottom: 10px;
        margin: 20px auto;
        display: inline-block;
        min-width: 400px;
    }
    .description-text {
        font-size: 18px;
        color: #34495e;
        margin: 30px auto;
        max-width: 700px;
        line-height: 1.6;
    }
    .title-text {
        font-size: 32px;
        font-weight: bold;
        color: #2c3e50;
        margin: 10px 0;
    }
    .footer {
        margin-top: 60px;
        width: 100%;
        display: table;
        table-layout: fixed;
    }
    .signature {
        display: table-cell;
        text-align: center;
        vertical-align: top;
    }
    .signature-line {
        border-top: 1px solid #2c3e50;
        width: 200px;
        margin: 0 auto 10px;
    }
    .date {
        color: #7f8c8d;
        margin-top: 20px;
        font-size: 14px;
    }
</style>
@endsection

@section('content')
<div class="certificate-container">
    <div class="default-border">
        <h1 class="header-text">Certificate of Achievement</h1>
        <div class="sub-header">PROUDLY PRESENTED TO</div>
        
        <div class="recipient-name text-center">
            {{ $certificate->recipient_name }}
        </div>
        
        <p style="font-size: 18px; color: #7f8c8d; margin-top: 20px;">For successful completion of</p>
        
        <h2 class="title-text text-center">{{ $certificate->title }}</h2>
        
        @if($certificate->description)
        <p class="description-text text-center">
            {{ $certificate->description }}
        </p>
        @endif
        
        <div class="footer">
            <div class="signature">
                <div style="height: 50px;"></div> <!-- Space for signature image -->
                <div class="signature-line"></div>
                <strong>Director</strong>
            </div>
            <div class="signature">
                <div style="height: 50px; display: flex; align-items: center; justify-content: center;">
                    <!-- Badge usually goes here -->
                    <div style="width: 60px; height: 60px; background: #f1c40f; border-radius: 50%; margin: 0 auto; display:flex; align-items:center; justify-content:center;">
                       <span style="font-size:10px; color:white;">SEAL</span>
                    </div>
                </div>
            </div>
            <div class="signature">
                <div style="height: 50px;"></div>
                <div class="signature-line"></div>
                <strong>Instructor</strong>
            </div>
        </div>
        
        <div class="date text-center">
            Issued on {{ \Carbon\Carbon::parse($certificate->issued_at)->format('F d, Y') }}
        </div>
    </div>
</div>
@endsection
