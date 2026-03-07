    <style type="text/css">
        .print-body {
            margin-top: 20px;
            margin-bottom: 25px;
        }

        /* hide print header/footer on screen; show only in print */
        .print-header,
        .print-footer {
            display: none;
        }

        /* Hide the repeating mini-header on screen; show only during print */
        thead .mini-header {
            display: none;
        }

        .print-footer {
            margin-top: 25px;
        }

        .footer-container {
            background-color: #ffffff;
            border: 1px solid #e0e0e0 !important;
            border-radius: 6px;
            padding: 15px;
            margin-top: 25px;
            font-size: 10px;
            color: #333;
            display: flex;
            flex-direction: column;
            /* stack on screen */
            gap: 12px;
        }

        .footer-container table td {
            width: 100%;
            vertical-align: top;
        }

        .footer-row {
            background-color: #ffffff !important;
            padding: 12px;
            border-radius: 4px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            line-height: 1.6;
            word-wrap: break-word;
            white-space: normal;
        }

        .footer-note {
            border-left: 3px solid #8d2d36 !important;
            padding-left: 8px;
        }

        .footer-signature {
            border-left: 3px solid #8d2d36 !important;
            text-align: center;
            padding: 15px 12px;
        }

        .footer-contact {
            border-left: 3px solid #8d2d36 !important;
        }

        /* Stack on mobile or narrow pages */
        @media (max-width: 700px) {

            .print-footer .footer-container,
            .footer-container {
                flex-direction: column;
            }

            .print-footer .footer-item,
            .footer-item {
                flex-basis: auto;
            }
        }

        .footer-contact div {
            margin-bottom: 5px;
            line-height: 1.6;
        }

        .footer-contact div:last-child {
            margin-bottom: 0;
        }

        .print-footer .footer-contact {
            text-align: right;
        }

        @media print {
            :root {
                --print-header-height: 30mm;
                /* header for A4 */
                --print-footer-height: 20mm;
                /* footer for A4 */
                /* bottom gap for the footer; used to lift footer above bottom so extra section can be shown */
                --print-footer-bottom-gap: 20mm;
                --print-page-width: 100%;
                --print-inner-width-mm: 100%;
            }

            /* @page removed - handled by Layout/print.blade.php */

            body {
                margin: 0;
                padding: 0;
                width: 100%;
                height: 100%;
            }

            /* html, body sizing removed - handled by Layout/print.blade.php */

            /* Layout calibration for thermal */
            .report-container {
                /* use full width for thermal printers */
                width: 100% !important;
                max-width: var(--print-inner-width-mm) !important;
                margin: 0 auto !important;
                padding: 0 !important;
                border: none !important;
                background: transparent !important;
            }

            /* Header/Footer positioning handled by the shared partial (print_header_footer.blade.php) */

            /* Give a bit of top breathing room when printing (helps when header is omitted) */
            .print-body {
                padding: 6mm 0 0 !important;
                margin: 0 !important;
            }

            /* Keep multiple-tests consistent */
            .multiple-tests.print-body {
                padding: 6mm 0 0 !important;
            }

            .footer-container {
                padding: 2px 0;
                margin: 0;
                display: flex;
                gap: 4px;
                width: 100%;
                align-items: center;
                justify-content: space-between;
            }

            .footer-row {
                margin-bottom: 2px;
                page-break-inside: avoid;
                display: block;
            }

            /* Per column widths are enforced by the grid above */
            .print-footer .footer-item {
                min-width: 0;
                box-sizing: border-box;
                page-break-inside: avoid;
                widows: 1;
                orphans: 1;
            }

            .footer-container table td {
                width: 100%;
                vertical-align: top;
            }

            /* Preserve all colors in print */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }

            /* Light backgrounds and borders must print */
            div[style*="background-color: #e9ecef"],
            div[style*="background-color:#e9ecef"],
            table[style*="border-bottom: 2px solid #8d2d36"],
            td[style*="border-left: 3px solid #8d2d36"],
            td[style*="border-right: 3px solid #8d2d36"],
            tr[style*="border-bottom: 2px solid #8d2d36"] {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }

            /* Preserve table styling */
            table {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                border-collapse: collapse !important;
                width: 100% !important;
            }

            /* Preserve row colors */
            tr[style*="background:"],
            tr[style*="background-color"] {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* Smaller fonts for multiple tests on thermal */
            .multiple-tests .personal-info-table {
                font-size: 14px !important;
            }

            .multiple-tests .personal-info-table th {
                font-size: 14px !important;
            }

            .multiple-tests .personal-info-table td {
                font-size: 14px !important;
            }

            .multiple-tests .pi-value {
                font-size: 14px !important;
            }

            .multiple-tests .pi-label {
                font-size: 14px !important;
            }

            .multiple-tests .section-title {
                font-size: 14px !important;
                padding: 4px 6px !important;
            }

            .multiple-tests .results-table {
                font-size: 14px !important;
            }

            .multiple-tests .results-table th {
                font-size: 14px !important;
            }

            .multiple-tests .results-table td {
                font-size: 14px !important;
            }

            .multiple-tests .footer-container {
                font-size: 14px !important;
            }

            .multiple-tests .footer-note {
                font-size: 14px !important;
            }

            /* Adjust notes section for multiple tests on thermal */
            .multiple-tests .test-notes-section {
                margin-bottom: 2px !important;
                padding: 2px !important;
                font-size: 15px !important;
            }

            .multiple-tests .notes-header {
                font-size: 14px !important;
                margin-bottom: 2px !important;
                padding-bottom: 2px !important;
            }

            .multiple-tests .notes-content {
                font-size: 14px !important;
                line-height: 1.1 !important;
            }

            .multiple-tests .clinical-notes-section {
                margin-bottom: 2px !important;
                padding: 2px !important;
                background-color: #ffffff !important;
            }

            .multiple-tests .clinical-notes-header {
                font-size: 14px !important;
                margin-bottom: 2px !important;
                padding-bottom: 2px !important;
            }

            .multiple-tests .dept-title {
                font-size: 14px !important;
                margin-bottom: 2px !important;
            }

            .multiple-tests .note-item {
                font-size: 14px !important;
                margin-bottom: 2px !important;
                line-height: 1.1 !important;
            }

            .multiple-tests .clinical-notes-dept {
                margin-bottom: 3px !important;
                padding-bottom: 2px !important;
            }

            /* Ensure proper page breaks */
            .test-title-section {
                page-break-inside: avoid;
            }

            .results-table {
                page-break-inside: auto;
            }

            /* Add space after page break */
            .results-table[style*="page-break-before"] {
                margin-top: 40px !important;
            }

            /* Repeat table headers on each page */
            thead {
                display: table-header-group;
            }

            tfoot {
                display: table-footer-group;
            }

            /* Keep mini-header hidden by default to avoid duplication with fixed header */
            thead .mini-header {
                display: none;
            }

            thead .mini-header th {
                background-color: #8d2d36 !important;
                color: #fff !important;
                padding: 8px 12px !important;
                font-weight: 700;
            }

            /* Small repeating lab heading for browsers that don't repeat fixed elements */

            /* Debug outlines to help visually align header/body/footer (remove later if OK) */
            /* Debug outlines were used for layout validation and are now removed */

            /* Prevent header/footer from splitting across pages */
            .print-header,
            .print-footer {
                page-break-inside: avoid;
            }

            .notes-section {
                page-break-inside: avoid;
            }
        }
    </style>

    {{-- Header and footer are included by Layout.print now to avoid duplication --}}
    <!-- end include print header/footer -->
    @php $maxRowsPerPage = 20; @endphp
    {{-- Personal information block (table format) --}}
    @if (!isset($skipPatientInfo) || !$skipPatientInfo)
        <style>
            .personal-info-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 2px; /* reduced from 5px */
    font-size: 14px;
    border: 2px solid #e7e7e7;
    border-radius: 6px;
    background: #fff;
    box-sizing: border-box;
}

.personal-info-table td,
.personal-info-table th {
    padding: 5px 8px; /* reduced padding */
    vertical-align: top;
    border: none;
}

.personal-info-table th {
    text-align: left;
    color: #8d2d36;
    font-weight: 600;
    width: 14rem;
    white-space: nowrap;
}

.personal-info-table td.value {
    padding-left: 6px; /* slightly reduced */
}

/* 🔑 Force single-line values (fixes Referred By wrapping) */
.personal-info-table .value {
    color: #333;
    text-decoration: underline;
    white-space: nowrap;        /* no line break */
    overflow: hidden;           /* hide overflow */
    text-overflow: ellipsis;    /* show ... if too long */
    max-width: 1px;             /* required for ellipsis in tables */
}

.personal-info-row {
    background: transparent;
}

@media print {
    .personal-info-table {
        font-size: 13px !important; /* slightly tighter for print */
    }

    .personal-info-table td,
    .personal-info-table th {
        padding: 4px 6px !important;
    }
}

        </style>
        <style>
            /* Personal card grid styles (screen and print friendly) */
            .personal-card {
                border-radius: 4px;
                background: #fff;
                border: 1px solid #e7e7e7;
                padding: 8px;
                margin-bottom: 6px;
                width: 100% !important;
                max-width: 100% !important;
                margin-left: auto;
                margin-right: auto;
                box-sizing: border-box;
            }

            .personal-card-inner {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 6px 12px;
            }

            .pi-cell {
                display: flex;
                gap: 10px;
                align-items: center;
            }

            .pi-cell i {
                color: #8d2d36;
                width: 30px;
                text-align: center;
            }

            .pi-meta {
                display: flex;
                flex-direction: column;
            }

            .pi-label {
                font-size: 10px;
                color: #8d2d36;
                font-weight: 700;
            }

            .pi-value {
                font-size: 10px;
                color: #333;
                font-weight: 600;
            }

            .section-title {
                background: #e9ecef;
                color: #333;
                border-radius: 4px;
                padding: 6px 8px;
                text-align: center;
                font-weight: 700;
                margin-bottom: 8px;
                display: block;
                font-size: 16px;
            }

            .section-title i {
                margin-right: 8px;
            }

            /* Results table styling */
            .results-table th {
                background: #fff;
                color: #333;
                font-weight: 700;
            }

            .results-table thead tr {
                border-bottom: 3px solid #8d2d36;
            }

            .results-table tbody tr td {
                padding: 12px;
            }

            /* Make the first column a bit darker (not pure black), for better readability */
            .results-table tbody tr td:first-child {
                color: #222 !important;
                font-weight: 400;
            }

            .results-table tbody tr td:nth-child(3) {
                color: #222 !important;
                font-weight: 400;
            }

            .results-table tbody tr td:nth-child(4) {
                color: #222 !important;
                font-weight: 400;
            }

            .results-table {
                border-radius: 4px;
                overflow: hidden;
                border: 1px solid #e6e6e6;
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box;
                font-size: 10px;
            }

            @media print {
                .personal-card {
                    border: 1px solid #e7e7e7 !important;
                }

                .section-title {
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }

                /* Adjust sizes for A4 printing */
                .results-table {
                    font-size: 14px !important;
                }

                .results-table th {
                    font-size: 14px !important;
                }

                .results-table td {
                    font-size: 14px !important;
                }

                .pi-value {
                    font-size: 14px !important;
                }

                .pi-label {
                    font-size: 14px !important;
                }

                .section-title {
                    font-size: 18px !important;
                    padding: 8px 10px !important;
                }

                /* Adjust header and footer fonts for A4 printing */

                .print-header .lab-subtitle {
                    font-size: 16px !important;
                }

                .print-header .lab-address {
                    font-size: 14px !important;
                }

                .print-header .contact-info {
                    font-size: 14px !important;
                }

                .print-header-details {
                    font-size: 14px !important;
                }

                .print-footer .footer-item {
                    font-size: 14px !important;
                }

                .print-footer .footer-signature strong {
                    font-size: 16px !important;
                }

                /* Adjust logo size for A4 printing */
                .print-header .print-inner table td img {
                    width: 32mm !important;
                    height: 32mm !important;
                }

                .print-header .print-inner table td img.header-logo {
                    width: 27mm !important;
                    height: 27mm !important;
                }

                .print-header .print-logo img.header-logo {
                    width: 30mm !important;
                    height: 30mm !important;
                }

                /* Adjust header and footer positioning for A4 */
                .print-header {
                    top: 10mm !important;
                }

                .print-footer {
                    bottom: 10mm !important;
                }
            }
        </style>
        {{-- Dual option styles removed --}}

        <table class="personal-info-table">
            <tbody>
                <tr class="personal-info-row">
                    <th><i class="fas fa-user-circle fa-lg" aria-hidden="true"></i> Patient Name</th>
                    <td class="value">{{ $patient->name ?? '-' }}</td>
                    <th><i class="fas fa-birthday-cake fa-lg" aria-hidden="true"></i> Age / Gender</th>
                    <td class="value">
                        @php
                            // Use individual age parts if available, otherwise use the combined string
                            if (
                                !empty($patient->age_years) ||
                                !empty($patient->age_months) ||
                                !empty($patient->age_days)
                            ) {
                                $parts = [];
                                if (!empty($patient->age_years)) {
                                    $parts[] = $patient->age_years . 'Y';
                                }
                                if (!empty($patient->age_months)) {
                                    $parts[] = $patient->age_months . 'M';
                                }
                                if (!empty($patient->age_days)) {
                                    $parts[] = $patient->age_days . 'D';
                                }
                                $ageDisplay = !empty($parts) ? implode(' ', $parts) : '0Y';
                            } else {
                                $ageDisplay = $patient->age ?: '-';
                            }
                        @endphp
                        {{ $ageDisplay }} / {{ ucfirst($patient->gender ?? '-') }}
                    </td>
                </tr>
                <tr class="personal-info-row">
                    <th><i class="fas fa-id-card fa-lg" aria-hidden="true"></i> Patient ID</th>
                    <td class="value">{{ $patient->patient_id ?? '-' }}</td>
                    <th><i class="fas fa-phone fa-lg" aria-hidden="true"></i> Mobile</th>
                    <td class="value">{{ $patient->mobile_phone ?? '-' }}</td>
                </tr>
                <tr class="personal-info-row">
                    <th><i class="fas fa-map-marker-alt fa-lg" aria-hidden="true"></i> Address</th>
                    <td class="value">{{ $patient->address ?? '-' }}</td>
                    <th><i class="fas fa-user-tie fa-lg" aria-hidden="true"></i> Referred By</th>
                    <td class="value">{{ $patient->referred_by ?? '-' }}</td>
                </tr>
                <tr class="personal-info-row">
                    <th><i class="fas fa-calendar-check fa-lg" aria-hidden="true"></i> Receiving Date</th>
                    <td class="value"><strong>{{ $patient->receiving_date ? $patient->receiving_date->format('d-M-Y H:i') : '-' }}</strong></td>
                    <th><i class="fas fa-calendar-alt fa-lg" aria-hidden="true"></i> Reporting Date</th>
                    <td class="value"><strong>{{ $patient->reporting_date ? $patient->reporting_date->format('d-M-Y H:i') : '-' }}</strong></td>
                </tr>
            </tbody>
        </table>
    @endif
    <hr style="border: 0; border-top: 1px solid #e7e7e7; margin: 8px 0 12px 0;">

    {{-- Title of the test category --}}
    <div class="section-title">
        <i class="fas fa-flask"></i>
        <span>{{ strtoupper($testEntry['name'] ?? 'TEST') }}</span>
    </div>
    @if (!empty($testEntry['department']))
        <div style="text-align: center; font-weight: 600; margin-bottom: 8px; font-size: 12px; color: #333;">
            Department: {{ $testEntry['department'] ?? '-' }}
        </div>
    @endif
    <div
        style="border: 1px solid #e0e0e0; border-radius: 6px; overflow: hidden; margin-bottom: 20px; width: 100%; max-width: 100%; margin-left:auto; margin-right:auto; box-sizing: border-box;">
        <table class="results-table" width="100%" cellpadding="5" cellspacing="0" style="border-collapse: collapse;">
            <thead>
                <!-- mini-header fallback removed to avoid duplication; use fixed header from partial -->
                <tr style="background-color: #fff !important; border-bottom: 3px solid #8d2d36 !important;">
                    <th style="text-align: left; padding: 12px; font-weight: bold; width: 35%; color: black;">
                        <i class="fas fa-tag" style="margin-right: 5px;"></i>Test Name
                    </th>
                    <th style="text-align: left; padding: 12px; font-weight: bold; width: 20%; color: black;">
                        <i class="fas fa-chart-line" style="margin-right: 5px;"></i>Results
                    </th>
                    <th style="text-align: left; padding: 12px; font-weight: bold; width: 15%; color: black;">
                        <i class="fas fa-balance-scale" style="margin-right: 5px;"></i>Unit
                    </th>
                    <th style="text-align: left; padding: 12px; font-weight: bold; width: 20%; color: black;">
                        <i class="fas fa-ruler" style="margin-right: 5px;"></i>Reference Ranges
                    </th>
                </tr>
            </thead>
            <tbody>
                @php
                    $analytes = $testEntry['saved_data']['analytes'] ?? [];
                    $analytes = is_array($analytes) ? $analytes : [];
                    $hasHL7Data = !empty($analytes);
                @endphp

                @if ($hasHL7Data)
                    <!-- HL7 Data -->
                    @php $chunks = array_chunk($analytes, $maxRowsPerPage ?? 10); @endphp
                    @foreach ($chunks as $chunkIndex => $chunk)
                        @if ($chunkIndex > 0)
            </tbody>
        </table>
        <table class="results-table" width="100%" cellpadding="5" cellspacing="0"
            style="border-collapse: collapse; page-break-before: always; margin-top: 40px;">
            <thead>
                <tr style="background-color: #fff !important; border-bottom: 3px solid #8d2d36 !important;">
                    <th style="text-align: left; padding: 12px; font-weight: bold; width: 35%; color: black;">
                        <i class="fas fa-tag" style="margin-right: 5px;"></i>Test Name
                    </th>
                    <th style="text-align: left; padding: 12px; font-weight: bold; width: 20%; color: black;">
                        <i class="fas fa-chart-line" style="margin-right: 5px;"></i>Results
                    </th>
                    <th style="text-align: left; padding: 12px; font-weight: bold; width: 15%; color: black;">
                        <i class="fas fa-balance-scale" style="margin-right: 5px;"></i>Unit
                    </th>
                    <th style="text-align: left; padding: 12px; font-weight: bold; width: 20%; color: black;">
                        <i class="fas fa-ruler" style="margin-right: 5px;"></i>Reference Ranges
                    </th>
                </tr>
            </thead>
            <tbody>
                @endif
                @php $rowCount = 0; @endphp
                @foreach ($chunk as $analyte)
                    @php $rowCount++; @endphp
                    <tr style="background: #ffffff;">
                        <td style="padding: 10px; border-bottom: 1px solid #e0e0e0; font-weight: 500;">
                            {{ $analyte['name'] ?? ($analyte['code'] ?? 'Unknown') }}
                        </td>
                        <td style="padding: 10px; border-bottom: 1px solid #e0e0e0; font-weight: 700; color: black;">
                            {{ $analyte['value'] ?? '' }}
                        </td>
                        <td style="padding: 10px; border-bottom: 1px solid #e0e0e0;">
                            {{ $analyte['units'] ?? '' }}
                        </td>
                        <td style="padding: 10px; border-bottom: 1px solid #e0e0e0;">
                            {{ $analyte['ref_range'] ?? '' }}
                        </td>
                    </tr>
                @endforeach
                @endforeach
            @else
                <!-- Actual Test Parameters -->
                @if (!empty($testEntry['template']['fields']))
                    @php
                        $fields = $testEntry['template']['fields'];
                        $chunks = array_chunk($fields, $maxRowsPerPage);
                    @endphp
                    @foreach ($chunks as $chunkIndex => $chunk)
                        @if ($chunkIndex > 0)
            </tbody>
        </table>
        <table class="results-table" width="100%" cellpadding="5" cellspacing="0"
            style="border-collapse: collapse; page-break-before: always; margin-top: 40px;">
            <thead>
                <tr style="background-color: #fff !important; border-bottom: 3px solid #8d2d36 !important;">
                    <th style="text-align: left; padding: 12px; font-weight: bold; width: 35%; color: black;">
                        <i class="fas fa-tag" style="margin-right: 5px;"></i>Test Name
                    </th>
                    <th style="text-align: left; padding: 12px; font-weight: bold; width: 20%; color: black;">
                        <i class="fas fa-chart-line" style="margin-right: 5px;"></i>Results
                    </th>
                    <th style="text-align: left; padding: 12px; font-weight: bold; width: 15%; color: black;">
                        <i class="fas fa-balance-scale" style="margin-right: 5px;"></i>Unit
                    </th>
                    <th style="text-align: left; padding: 12px; font-weight: bold; width: 20%; color: black;">
                        <i class="fas fa-ruler" style="margin-right: 5px;"></i>Reference Ranges
                    </th>
                </tr>
            </thead>
            <tbody>
                @endif
                @php $rowCount = 0; @endphp
                @foreach ($chunk as $field)
                    @php
                        $value = $testEntry['saved_data'][$field['name']] ?? '';
                        $label = $field['label'] ?? 'Unknown';
                        $unit = $field['unit'] ?? '';
                        $ref = $field['ref'] ?? '';
                        $fieldType = $field['type'] ?? 'text';
                        $rowCount++;

                        // Format date values to d-M-Y
                        if ($value && preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
                            try {
                                $dateTime = new \DateTime($value);
                                $value = $dateTime->format('d-M-Y');
                            } catch (\Exception $e) {
                                // Leave as-is if invalid
                            }
                        }

                        // Standardized display: simply use the saved value for any field type
                        $displayValue = $value;
                    @endphp
                    <tr style="background: #ffffff;">
                        <td style="padding: 10px; border-bottom: 1px solid #e0e0e0; font-weight: 500;">
                            {{ $label }}</td>
                        <td style="padding: 10px; border-bottom: 1px solid #e0e0e0; font-weight: 700; color: black;">
                            {!! $displayValue !!}
                            {{-- Dual option options removed from print --}}
                        </td>
                        <td style="padding: 10px; border-bottom: 1px solid #e0e0e0;">{{ $unit }}</td>
                        <td style="padding: 10px; border-bottom: 1px solid #e0e0e0;">{{ $ref }}</td>
                    </tr>
                @endforeach
                @endforeach
            @else
                {{-- No template; print flattened saved data key/value pairs as a fallback --}}
                @php
                    $sd = $testEntry['saved_data'] ?? [];
                    $chunks = array_chunk($sd, $maxRowsPerPage);
                @endphp
                @foreach ($chunks as $chunkIndex => $chunk)
                    @if ($chunkIndex > 0)
            </tbody>
        </table>
        <table class="results-table" width="100%" cellpadding="5" cellspacing="0"
            style="border-collapse: collapse; page-break-before: always; margin-top: 40px;">
            <thead>
                <tr style="background-color: #fff !important; border-bottom: 3px solid #8d2d36 !important;">
                    <th style="text-align: left; padding: 12px; font-weight: bold; width: 35%; color: black;">
                        <i class="fas fa-tag" style="margin-right: 5px;"></i>Test Name
                    </th>
                    <th style="text-align: left; padding: 12px; font-weight: bold; width: 20%; color: black;">
                        <i class="fas fa-chart-line" style="margin-right: 5px;"></i>Results
                    </th>
                    <th style="text-align: left; padding: 12px; font-weight: bold; width: 15%; color: black;">
                        <i class="fas fa-balance-scale" style="margin-right: 5px;"></i>Unit
                    </th>
                    <th style="text-align: left; padding: 12px; font-weight: bold; width: 20%; color: black;">
                        <i class="fas fa-ruler" style="margin-right: 5px;"></i>Reference Ranges
                    </th>
                </tr>
            </thead>
            <tbody>
                @endif
                @php $rowCount = 0; @endphp
                @foreach ($chunk as $k => $v)
                    @php $rowCount++; @endphp
                    <tr style="background: #ffffff;">
                        <td style="padding: 10px; border-bottom: 1px solid #e0e0e0; font-weight: 500;">
                            {{ $k }}</td>
                        <td style="padding: 10px; border-bottom: 1px solid #e0e0e0; font-weight: 700; color: black;">
                            {{ $v }}</td>
                        <td style="padding: 10px; border-bottom: 1px solid #e0e0e0;">&nbsp;</td>
                        <td style="padding: 10px; border-bottom: 1px solid #e0e0e0;">&nbsp;</td>
                    </tr>
                @endforeach
                @endforeach
                @endif
                @endif
            </tbody>
        </table>
    </div>

    <!-- Test Category Notes Section (Lab Test Category Notes) -->
    @php
        $categoryNotes = null;
        // Fetch notes from LabTestCat based on test name
        try {
            $labTestCategory = \App\Models\LabTestCat::where('cat_name', $testEntry['name'])->first();
            $categoryNotes = $labTestCategory ? $labTestCategory->notes : null;
        } catch (\Exception $e) {
            $categoryNotes = null;
        }
    @endphp
    @if ($categoryNotes)
        <x-enhanced-notes :notes="$categoryNotes" title="Test Notes & Remarks" :show-icon="true" class="test-notes-section"
            style="margin-bottom: 20px;" />
    @endif

    <!-- Test Notes Section (if any notes exist) -->
    @php
        $billNotes = [];
        // Collect notes from the bill's all_test JSON if available
if (isset($bill) && $bill->all_test) {
    $allTests = json_decode($bill->all_test, true);
    if (is_array($allTests)) {
        foreach ($allTests as $test) {
            if (!empty($test['notes'])) {
                $dept = $test['department'] ?? 'General';
                if (!isset($billNotes[$dept])) {
                    $billNotes[$dept] = [];
                }
                $billNotes[$dept][] = [
                    'test_name' => $test['test_name'] ?? '',
                    'notes' => $test['notes'],
                        ];
                    }
                }
            }
        }
    @endphp

    @if (!empty($billNotes))
        <div class="clinical-notes-section"
            style="background-color: #ffffff !important; border: 2px solid #f39c12 !important; border-radius: 6px; padding: 15px; margin-bottom: 20px;">
            <div class="clinical-notes-header"
                style="font-weight: bold; color: #d68910; font-size: 10px; margin-bottom: 10px; border-bottom: 1px solid #f39c12; padding-bottom: 8px;">
                <i class="fas fa-sticky-note" style="margin-right: 8px;"></i>Clinical Notes & Remarks
            </div>
            @foreach ($billNotes as $department => $notesList)
                <div class="clinical-notes-dept"
                    style="margin-bottom: 12px; padding-bottom: 10px; border-bottom: 1px dashed #f39c12;">
                    <div class="dept-title"
                        style="font-weight: bold; color: #d68910; font-size: 11px; margin-bottom: 6px;">
                        <i class="fas fa-building" style="margin-right: 5px;"></i>{{ $department }}
                    </div>
                    @foreach ($notesList as $note)
                        <div class="note-item" style="margin: 8px 0;">
                            <strong style="color: #555;">{{ $note['test_name'] }}:</strong>
                            <div style="margin-left: 0; margin-top: 4px;">
                                <x-enhanced-notes :notes="$note['notes']" :show-icon="false" title="" class="" />
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    @endif

    <!-- Footer will be provided by print_header_footer partial (already included above) -->

    <!-- New section below footer -->
    <div class="extra-section-below-footer">
        <h4>Additional Notes</h4>
        <p>This is a new section below the footer. It should appear in print after the footer.</p>
    </div>

    <style>
        @media print {
            .extra-section-below-footer {
                width: var(--print-inner-width-mm);
                margin: 0 auto;
                padding: 6px;
                font-size: 10px;
                border-top: 1px solid #ddd;
                margin-top: 10px;
            }
        }
        /* Hide on screen */
        .extra-section-below-footer {
            display: none;
        }
    </style>
