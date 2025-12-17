

    <body>
        
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="../web/frontController.php">
            <fieldset>

                <legend>Bienvenu!</legend>
                <p>
                    
                    <label for="Prenom_id">Prenom</label> :
                    <br>
                    <input type="text" placeholder="xxxx" name="Prenom" id="Prenom_id" required/>
                    
                    <label for="nom_id">Nom</label>:
                    <br>
                    <input type="text" placeholder="xxxx" name="nom" id="nom_id" required/>

                    <label for="email_id">email</label>:
                    <br>
                    <input type ="email" placeholder="votre@exemple.com" name = "email" id="email_id" required/>

                    <label for="mdp_id">Mot de passe</label>:
                    <br>
                    <input type ="password" placeholder="mot de passe" name = "mdp" id="mdp_id" required/>

                    <label for="Cmdp_id">Confirmation du mot de passe</label>:
                    <br>
                    <input type ="password" placeholder="confirmation" name = "Cmdp" id="Cmdp_id" required/>


                    <label for="ProfilUtilisateur">Choisissez votre profil de compte </label>
                    <br>
    
                    <select name="ProfilUtilisateur" id="ProfilUtilisateur">
        
                    <option value="Chercheur">Chercheur</option>
        
                    <option value="Etudiant">Etudiant</option>
        
                    <option value="GrandPUblic">Grand Public</option>


                </select>

                    
                </p>
                <p>
                    <input type='hidden' name='action' value='created'>
                    <input type='hidden' name='controller' value='utilisateur'>

                    <input type="submit" value="S'inscrire" />
                </p>

                <p> Vous avez déjà un compte? <a href="../web/frontController.php?action=connexion&controller=utilisateur">Connectez-vous</a></p>
            </fieldset>
        </form>
    </body>
