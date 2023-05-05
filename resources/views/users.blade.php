<html>
<head>
<style>
body {
    font-family: arial;
}
#users {
    width: 100%;
}
#users th {
    text-align: left;
}
#users td {
    padding: 3px;
}
</style>
</head>
<body>

<table id="users">
<thead>
    <tr>
        <th>External ID</th>
        <th>Username</th>
        <th>First Name</th>
        <th>Last Name</th>
    </tr>
</thead>
<tbody>

</tbody>
</table>

<script src="/assets/jquery-3.6.4.min.js"></script>
<script>

$(function(){

    const userRowTemplate = user => `<tr>
        <td>${user.external_id}</td>
        <td>${user.username}</td>
        <td>${user.first_name}</td>
        <td>${user.last_name}</td>
    </tr>`;

    function loadUsers() {

        $.getJSON("/api/users/<?= $_GET['tenant'] ?>", function(data){

            $("#users tbody tr").remove();
            for(var i in data.users) {
                var user = data.users[i];
                $("#users").append(userRowTemplate(user));
            }

        });

        setTimeout(loadUsers, 1000);
    }

    loadUsers();

});

</script>

</body>
</html>
