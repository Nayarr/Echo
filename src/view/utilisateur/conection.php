<body>
    <?php if (!empty($error)): ?>
        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="../web/frontController.php">
        <fieldset>
            <legend>Connexion</legend>
            <p>
                <label for="email_id">Email</label>:
                <br>
                <input type="email" name="email" id="email_id" placeholder="votre@exemple.com" required />

                <label for="mdp_id">Mot de passe</label>:
                <br>
                <input type="password" name="mdp" id="mdp_id" placeholder="mot de passe" required />
            </p>

            <p>
                <input type="hidden" name="action" value="traiterConnexion">
                <input type="hidden" name="controller" value="utilisateur">
                <input type="submit" value="Se connecter" />
            </p>

            <p>Pas encore de compte ? <a href="../web/frontController.php?action=inscription&controller=utilisateur">Inscrivez-vous</a></p>
        </fieldset>
    </form>
</body>
