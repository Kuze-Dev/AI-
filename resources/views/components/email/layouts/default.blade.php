<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <style>
        .main-container {
            padding-top: 2em;
            padding-bottom: 2em;
            padding-left: 16em;
            padding-right: 16em;
            font-size: medium;
            font-family: 'Arial', 'Helvetica', 'Impact', 'Haettenschweiler', 'Arial Narrow Bold', sans-serif;
        }

        .header {
            margin: auto;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1.3px solid #e5e7eb;
            padding-bottom: 1em;
            font-family: 'Arial', 'Helvetica', 'Impact', 'Haettenschweiler', 'Arial Narrow Bold', sans-serif;
        }

        .header img {
            width: 100px;
            height: 100px;
            object-fit: cover
        }

        .header .col-2 {
            padding-left: 1em;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: flex-start;

        }

        .title {
            font-size: xx-large;
            font-weight: bold;
        }

        .subtitle {
            color: #3f3f3f;
            padding-left: 4px;
        }

        .card {
            border: 1.5px solid #E5E7EB;
            margin-top: 1.5em;
            padding-top: 0.5em;
            padding-bottom: 1.5em;
            padding-left: 1.3em;
            padding-right: 1.3em;
            text-align: left;
            border-radius: 0.8em;
        }

        .card-title {
            border-bottom: 1.3px solid #E5E7EB;
            margin-bottom: 0.3em;
        }

        .card-title p {
            font-weight: bold
        }

        .summary-list {
            display: flex;
            justify-content: space-between
        }

        .horizontal-line {
            border-bottom: 1.3px solid #E5E7EB
        }

        .order-details {
            display: flex;
            justify-content: flex-start;
            border-bottom: 1.3px solid #E5E7EB;
            padding-bottom: 0.5em;
            align-items: flex-start;
        }

        .order-details img {
            width: 120px;
            height: 120px;
            object-fit: cover
        }

        .footer {
            margin: auto;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }

        .button-link {
            display: inline-block;
            padding: 10px 20px;
            background-color: #40414b;
            color: #fff;
            text-decoration: none;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
            font-weight: bold;
            transition: background-color 0.3s;

            :hover {
                background-color: #258cd1;
            }
        }

        .footer p {
            margin: 0;
            padding: 0;
        }

        @media (max-width: 1024px) and (min-width: 769px) {
            .main-container {
                padding-left: 8em;
                padding-right: 8em;
            }
        }

        @media (max-width: 768px) {
            .main-container {
                padding-left: 1.5em;
                padding-right: 1.5em;
            }

            .summary-list .col-1 {
                width: 70%;
            }

            .summary-list .col-2 {
                width: 30%;
            }
        }

        @media (max-width: 576px) {
            .main-container {
                padding-left: 2px;
                padding-right: 2px;
                font-size: small;
            }

            .summary-list {
                padding: 0.8em;
            }

            .summary-list .col-1 {
                width: 50%;
            }

            .summary-list .col-2 {
                width: 50%;
            }

            .order-details img {
                width: 80px;
                height: 80px
            }

            .title {
                font-size: medium;
            }

            .subtitle {
                font-size: small;
            }

            .header img {
                width: 70px;
                height: 70px;
                object-fit: cover
            }
        }
    </style>
</head>
<body>
    {{$slot}}
</body>
</html>