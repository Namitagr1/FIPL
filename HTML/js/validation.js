const validation = new JustValidate("#signup");

validation
    .addField("#name", [
        {
            rule: "required"
        }
    ])
    .addField("#team_name", [
        {
            rule: "required"
        },
        {
            rule: 'maxLength',
            value: 30,
            errorMessage: "Team Name exceeds maximum length (30 characters)"
        },
        {
            validator: (value) => value.split(' ').filter(el => {return el !== ''}).length === 2,
            errorMessage: "Team Name must be 2 words long"
        },
        {
            validator: (value) => () => {
                return fetch("validate-team-name.php?team_name=" + encodeURIComponent(value))
                       .then(function(response) {
                           return response.json();
                       })
                       .then(function(json) {
                           return json.available;
                       });
            },
            errorMessage: "Team Name Already Taken"
        }
    ])
    .addField("#email", [
        {
            rule: "required"
        },
        {
            rule: "email"
        },
        {
            validator: (value) => () => {
                return fetch("validate-email.php?email=" + encodeURIComponent(value))
                       .then(function(response) {
                           return response.json();
                       })
                       .then(function(json) {
                           return json.available;
                       });
            },
            errorMessage: "Email Already Taken"
        }
    ])
    .addField("#password", [
        {
            rule: "required"
        },
        {
            rule: "password"
        }
    ])
    .addField("#password_confirmation", [
        {
            rule: "required"
        },
        {
            validator: (value, fields) => {
                return value === fields["#password"].elem.value;
            },
            errorMessage: "Passwords Should Match"
        }
    ])
    .onSuccess((event) => {
        document.getElementById("signup").submit();
    });