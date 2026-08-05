<style>
    @page { margin: 30px 34px 46px; }

    * { box-sizing: border-box; }
    body {
        margin: 0;
        font-family: DejaVu Sans, sans-serif;
        font-size: 10px;
        line-height: 1.45;
        color: #25364a;
        background: #ffffff;
    }

    .ceot-header {
        width: 100%;
        margin: 0 0 18px;
        border-collapse: separate;
        border-spacing: 0;
        background: #eef8ff;
        border: 1px solid #cbe8f8;
        border-bottom: 3px solid #0878c9;
        border-radius: 10px;
    }
    .ceot-header td { border: 0; padding: 13px 15px; vertical-align: middle; }
    .ceot-brand-cell { width: 42%; }
    .ceot-mark {
        display: inline-block;
        padding: 8px 10px;
        margin-right: 8px;
        border-radius: 8px;
        background: #075aa5;
        color: #ffffff;
        font-size: 14px;
        font-weight: 700;
        letter-spacing: .8px;
    }
    .ceot-brand-copy { display: inline-block; vertical-align: middle; }
    .ceot-brand-name { color: #064d8c; font-size: 14px; font-weight: 700; letter-spacing: .5px; }
    .ceot-brand-tagline { margin-top: 2px; color: #5e7891; font-size: 8px; text-transform: uppercase; letter-spacing: .7px; }
    .ceot-document-cell { text-align: right; }
    .ceot-kicker { color: #0b82ca; font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
    .ceot-title { margin-top: 2px; color: #14324d; font-size: 18px; font-weight: 700; line-height: 1.15; }
    .ceot-subtitle { margin-top: 4px; color: #61778c; font-size: 9px; }

    .ceot-meta {
        margin: -5px 0 18px;
        padding: 9px 11px;
        background: #f7fafc;
        border-left: 3px solid #11a8b8;
        color: #526a80;
        font-size: 9px;
    }
    .ceot-meta strong { color: #20384e; }

    .ceot-section {
        margin: 18px 0 8px;
        color: #075aa5;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .55px;
        border-bottom: 1px solid #cbe8f8;
        padding-bottom: 5px;
    }
    .ceot-note {
        padding: 10px 12px;
        background: #f6fbfe;
        border: 1px solid #d9edf8;
        border-radius: 7px;
        color: #405a70;
    }

    .ceot-table { width: 100%; border-collapse: collapse; margin: 0 0 15px; }
    .ceot-table thead { display: table-header-group; }
    .ceot-table tr { page-break-inside: avoid; }
    .ceot-table th {
        padding: 7px 8px;
        border: 1px solid #d4e4ef;
        background: #eaf5fb;
        color: #174b72;
        font-size: 8px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .35px;
        text-align: left;
    }
    .ceot-table td { padding: 7px 8px; border: 1px solid #e2ebf1; vertical-align: top; }
    .ceot-table tbody tr:nth-child(even) { background: #f9fcfe; }
    .text-right { text-align: right !important; }
    .text-center { text-align: center !important; }
    .font-bold { font-weight: 700; }
    .muted { color: #71869a; }
    .small { font-size: 8px; }

    .ceot-stats { width: 100%; border-collapse: separate; border-spacing: 5px; margin: 0 -5px 14px; }
    .ceot-stats td {
        padding: 10px 7px;
        text-align: center;
        background: #f5faff;
        border: 1px solid #d9ebf5;
        border-radius: 7px;
    }
    .ceot-stat-value { display: block; color: #075aa5; font-size: 15px; font-weight: 700; }
    .ceot-stat-label { display: block; margin-top: 3px; color: #698095; font-size: 7px; font-weight: 700; text-transform: uppercase; letter-spacing: .35px; }

    .ceot-badge { display: inline-block; padding: 3px 6px; border-radius: 10px; font-size: 7px; font-weight: 700; text-transform: uppercase; }
    .badge-blue { background: #dceeff; color: #075aa5; }
    .badge-green { background: #dff7ed; color: #08785c; }
    .badge-amber { background: #fff2cf; color: #956000; }
    .badge-red { background: #ffe3e7; color: #a72a42; }
    .badge-slate { background: #edf1f5; color: #536578; }
    .badge-cyan { background: #dff7fa; color: #08737e; }

    .ceot-total-card {
        padding: 12px 14px;
        background: #eef8ff;
        border: 1px solid #cbe8f8;
        border-radius: 8px;
        page-break-inside: avoid;
    }
    .ceot-total-table { width: 100%; border-collapse: collapse; }
    .ceot-total-table td { padding: 4px 0; border: 0; }
    .ceot-total-row td { padding-top: 8px; border-top: 2px solid #96cfe9; color: #075aa5; font-size: 13px; font-weight: 700; }

    .ceot-footer {
        position: fixed;
        left: 0;
        right: 0;
        bottom: -31px;
        color: #7890a4;
        font-size: 7px;
        border-top: 1px solid #d9e7ef;
        padding-top: 6px;
    }
    .ceot-footer table { width: 100%; border-collapse: collapse; }
    .ceot-footer td { padding: 0; border: 0; }
    .ceot-footer-right { text-align: right; }
</style>
