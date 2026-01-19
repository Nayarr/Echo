<div style="max-width: 500px; margin: 20px auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
    <h2 style="text-align: center;">Connexion</h2>
    
    <?php if (isset($error)): ?>
        <div style="color: white; background-color: #dc3545; padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="frontController.php">
        <fieldset style="border:none; padding:0;">
            
            <label>Email :</label>
            <input type="email" name="email" placeholder="votre@email.com" required style="width: 100%; padding: 8px; margin-bottom: 10px;">

            <label>Mot de passe :</label>
            <input type="password" name="mdp" placeholder="********" required style="width: 100%; padding: 8px; margin-bottom: 20px;">

            <input type="hidden" name="controller" value="utilisateur">
            <input type="hidden" name="action" value="traiterConnexion">

            <button type="submit" style="width: 100%; padding: 10px; background-color: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer;">
                Se connecter
            </button>
        </fieldset>
    </form>

    <p style="text-align: center; margin-top: 15px;">
        Pas encore de compte ? <a href="frontController.php?controller=utilisateur&action=inscription">Inscrivez-vous</a>
    </p>
</div>