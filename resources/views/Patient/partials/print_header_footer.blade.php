<style>
    @media print {
        :root {
            --print-header-height: 32mm;
            --print-footer-height: 45mm;
            /* Gap to lift footer above the page bottom to leave space below it for an extra section */
            --print-footer-bottom-gap: 45mm; /* Adjust this value to add/remove the space below footer */
        }

        .print-header,
        .print-footer {
            position: fixed;
            left: 0;
            right: 0;
            width: auto;
            padding: 0;
            z-index: 200000;
            box-sizing: border-box;
            max-width: none;
            display: block !important; /* Ensure visible when printing */
        }

        .print-header .lab-name {
            font-size: 24px !important;
            white-space: nowrap !important;
        }

        .print-header .lab-subtitle {
            font-size: 20px !important;
        }
        .print-header .lab-address  {
            font-size: 20px !important;
            line-height: 1.2 !important;
        }

        .print-header .lab-contact  {
            font-size: 16px !important;
            padding-top: 4px !important;
        }

        .print-header .contact-info {
            font-size: 24px !important;
        }

        .print-header-details {
            font-size: 24px !important;
        }

        .print-footer .footer-item {
            font-size: 9px !important;
        }

        .print-footer .footer-signature strong {
            font-size: 10px !important;
        }

        .print-header-details {
            display: block !important;
            width: auto;
            padding: 0;
        }

        .print-header .print-inner table td {
            vertical-align: middle !important;
        }

        .print-header .print-inner table td.header-logo-cell {
            display: none;
            
        }

        .print-header .print-inner table td img.header-logo {
            width: 30mm !important;
            height: 30mm !important;
        }

        .print-header .print-inner .print-logo img.header-logo {
            width: 30mm !important;
            height: 30mm !important;
            border-radius: 50% !important;
            border: 2px solid #8d2d36 !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1) !important;
        }

        .print-header .print-inner table td.center-cell {
            text-align: right !important;
        }

        .print-header {
            top: 2mm;
            height: var(--print-header-height);
        }

        .print-footer {
            /* place the footer above the bottom by a configurable gap so things can be placed below it */
            bottom: var(--print-footer-bottom-gap);
            height: var(--print-footer-height);
        }

        /* A print-only spacer div that can create dedicated vertical space below the fixed footer; useful when adding a new section below the footer. */
        .post-footer-space {
            display: block;
            height: var(--print-footer-bottom-gap);
            width: var(--print-inner-width-mm);
            margin: 0 auto;
        }

        .print-header,
        .print-footer {
            page-break-inside: avoid;
        }

        .footer-note .note-line {
            padding-left: 27px;
            /* move second line right (like mr-2) */
            text-indent: -14px;
            /* keeps first line aligned with icon */
            display: block;
            /* ensures line behaves correctly */
        }

    }
</style>

<!-- Print Header -->
<div class="print-header" style="display: none; padding: 0;">
    <div class="print-inner"
        style="
            width: var(--print-inner-width-mm);
            margin: 0 auto;
            padding: 0;
            box-sizing: border-box;
            position: relative;
        ">

        <table width="100%" cellpadding="0" cellspacing="0"
            style="border-bottom: 6px solid #8d2d36; padding-bottom: 8px; margin-top: 5px;">
            <tr>
                <td width="10%" valign="middle" align="left" class="logo-cell"
                    style="
                        padding: 0 6px;
                        vertical-align: middle;
                        
                    ">
                    <img src="{{ asset('assets/images/logo.png') }}" class="header-logo" alt="Logo"
                        style="
                            width: 35mm;
                            height: 35mm;
                            border-radius: 50%;
                            border: 3px solid #8d2d36;
                            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                        " />
                </td>
                <td class="center-cell" style="padding: 0 6px; text-align: left; vertical-align: middle;">
                    <div class="lab-name"
                        style="
                        text-align: left;
                            font-weight: bold;
                            margin: 0;
                            line-height: 1.1;
                            color: #8d2d36;
                        ">
                        NEW MODERN CLINICAL LABORATORY
                        <div class="lab-subtitle"
                        style="
                            text-align: left;
                            margin: 2px 0 0 0;
                            font-weight: 600;
                            color: #8d2d36;
                        ">
                        (KP HCC) REG: 03663 SWAT 
                    </div>
                    <div class="lab-contact">
                <!-- <hr style="border: 1px solid #000; margin: 5px 0;"> -->
                
                    <p>
                        📞 0302-8080191  📞 0313-9797790  ✉  bachakhanacl@gmail.com <br> 📍 Kabal Road, Near Township Chowk, Kanju Swat
                    </p>
                
            </div>
                    </div>
                </td>
                <td width="20%" style="padding-left: 4px; vertical-align: middle;">
                    <div class="lab-address"
                        style="
                        text-align: left;
                            color: #8d2d36;
                            margin: 0;
                            line-height: 1.1;
                        ">
                        <strong style="font-size: 20px ">Bacha Khan</strong> <br /> BS Pathology (KMU)
                        DMLT KPK Peshawar CT Pathology Department Saidu Medical
                        College/ SGTH Swat
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>
<div class="post-footer-space" aria-hidden="true" style="display: none;"></div>

<!-- Print Header Details -->
<div class="print-header-details"
    style="display: none; width: var(--print-inner-width-mm); margin: 0 auto; padding: 0;">
    <table width="100%" cellpadding="0" cellspacing="0" style="font-size: 11px;">
        <tr>
            <td align="left" style="padding: 6px 0; color: #444; display: none;">
                {{-- patient info intentionally removed --}}
            </td>
            <td align="right" style="padding: 6px 0; color: #444;">
                <!-- optional test info removed -->
            </td>
        </tr>
    </table>
</div>

<!-- Print Footer -->
<div class="print-footer" style="display: none; padding: 0;">
    <div class="print-inner"
        style="
            width: var(--print-inner-width-mm);
            margin: 0 auto;
            padding: 0;
            box-sizing: border-box;
        ">
        <div class="footer-container"
            style="
                padding: 4px;
                margin: 0;
                display: flex;
                gap: 6px;
                width: 100%;
                align-items: center;
                justify-content: space-between;
            ">
            <div class="footer-item footer-note"
                style="
                    background-color: #f8fafb;
                    padding: 4px;
                    border-left: 2px solid #8d2d36;
                    font-size: 9px; 
                    flex: 1 1 62%;
                ">
                <strong style="color: black;">Please Note:</strong><br />

                <div class="note-line  ">
                    <i class="fas fa-info-circle" style="margin-right: 5px; color: black; "></i>
                    If there is no correlation with your clinical findings then please do ask this lab
                    to repeat the test on the same sample, as we preserve it till late evening.
                    
                </div>

                 <div style="margin-top: 4px; font-size: 16px">
                    <i class="fas fa-signature" style="margin-right: 5px; color: black;"></i>
                    This report is not meant for any medico-legal purpose.
                </div>

                <div style="margin-top: 4px;font-size: 16px">
                    <i class="fas fa-signature" style="margin-right: 5px; color: black;"></i>
                    This is a digitally signed report by
                    <strong style="color: black; font-size: 16px;">Bacha Khan</strong>
                    and does not require manual signature.
                </div>
            </div>
        </div>
    </div>
</div>
