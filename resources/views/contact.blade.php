<html>
<head>
    <style>
        .container {
            padding: 10px;
        }

        #contact {
            border-collapse: collapse;
            width: 100%;
        }

        #contact td,
        #contact th {
            border: 1px solid #ddd;
            padding: 8px;
            width: 30%;
        }

        #contact th {
            width: 10%;
        }

        #contact tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        #contact tr:hover {
            background-color: #ddd;
        }

        #contact th {
            padding-top: 12px;
            padding-bottom: 12px;
            text-align: left;
            background-color: #8062c7;
            color: white;
        }
        .heading {
            background-color: lightgray;
            padding: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="heading">
            <h1>{{ $contact_data['subject'] }}</h1>
        </div>
        <table id="customers">
            <tr>
                <th>Full Name</th>
                <td>{{ $contact_data['fullname'] }}</td>
            </tr>
            <tr>
                <th>Email</th>
                <td>{{ $contact_data['email'] }}</td>
            </tr>
            <tr>
                <th>Message</th>
                <td>{{ $contact_data['message'] }}</td>
            </tr>
        </table>
    </div>
</body>

</html>
