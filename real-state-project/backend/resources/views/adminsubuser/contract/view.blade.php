<!DOCTYPE html>
<html>

<head>
    <style>
        /* Reset some basic styles */
        body {
            font-family: Arial, sans-serif;
            box-sizing: border-box;
        }

        .input_field {
            display: block;
        }

        p {
            white-space: nowrap !important;
        }

        @media print {
            .input_field {
                background: #eef2f2 !important;
                border: 1px solid #e3eded !important;
                padding: 0px !important;
                color: black;
                flex-direction: row;
                /* width: 75%; */
                display: -webkit-box !important;
                text-align: center;
                min-height: 1.2rem;
                -webkit-box-orient: vertical;
            }

            .p50 {
                padding: 5vw;
            }

            .w10 {
                width: 10% !important;
            }

            .w20 {
                width: 15% !important;
            }

            .w30 {
                width: 20% !important;
            }

            .w40 {
                width: 30% !important;
            }

            .w50 {
                width: 35% !important;
            }

            .w60 {
                width: 40% !important;
            }

            .w70 {
                width: 45% !important;
            }

            .w80 {
                width: 50% !important;
            }

            .w-80 {
                width: 50% !important;
            }

            .w-90 {
                width: 60% !important;
            }

            small {
                font-size: 10px !important;
            }

            p,
            li {
                align-items: center;
                position: relative !important;
                font-size: 12px;
                flex-direction: row;
                flex-wrap: nowrap;
            }

            p {
                display: ruby !important
            }

            ul li,
            ol li,
            dl li {
                line-height: 1.8;
            }

            .justify-content-center {
                justify-content: center !important;
            }

            .reverse {
                justify-content: end !important;
                margin: 0px !important;
                padding: 0px !important;
                align-items: center !important;
            }

            .p0 {
                padding: 0px !important;
            }

            .m0 {
                margin: 0px !important;
            }

            .checkbox {
                width: 2% !important;
                border: none !important;
                background-color: transparent !important;
            }

            .custom_css {
                display: flex !important;
                justify-content: space-between;
                align-items: center !important;
                width: 100%;
            }

            table tbody tr td {
                border: none;
                padding: 7px 12px 7px 12px !important;
                font-size: 12px !important;
            }

            table thead tr th {
                background-color: #eef2f2 !important;
                padding: 10px 12px 10px 12px !important;
            }

            .note-editor .note-editing-area .note-editable table td,
            .note-editor .note-editing-area .note-editable table th {
                border: none !important;
            }

            small {
                margin-left: 5px;
            }
        }
    </style>
</head>

<body>
    {!! $structure !!}
</body>

</html>
