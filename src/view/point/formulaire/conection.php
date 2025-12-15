

    <body>
        
        <form method="GET" action="../web/frontController.php">
            <fieldset>

                <legend>Bienvenue!</legend>
                <p>
                    
                    <label for="email_id">Adresse mail:</label> :
                    <input type="text" placeholder="squeezie" name="email" id="email_id" required/>
                    
                    <label for="mdp_id">Mot de passe</label>:
                    <input type="text" placeholder="hauchar" name="mdp" id="mdp_id" required/>
                    
                </p>
                <p>
                    <input type='hidden' name='action' value='conecter'>

                    <input type="submit" value="Sign in" />
                </p>
                <p> Vous n'avez pas de compte? <a href="inscription.php">Inscrivez-vous</a></p>

            </fieldset>
        </form>
    </body>
