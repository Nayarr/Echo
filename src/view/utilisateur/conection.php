<div class="glass-panel form-card">
    <h2>Connexion</h2>
    
    <?php if (isset($error)): ?>
        <div class="alert-error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="frontController.php">
        
        <div class="form-group">
            <label for="email_id">Email</label>
            <input type="email" name="email" id="email_id" placeholder="votre@email.com" required 
                   value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
        </div>

        <div class="form-group">
            <label for="mdp_id">Mot de passe</label>
            <input type="password" name="mdp" id="mdp_id" placeholder="••••••••" required>
        </div>

        <input type="hidden" name="controller" value="utilisateur">
        <input type="hidden" name="action" value="traiterConnexion">

        <button type="submit" class="btn-submit">
            Se connecter
        </button>
    </form>

    <div class="form-footer">
        <p>Pas encore de compte ?</p>
        <a href="frontController.php?controller=utilisateur&action=inscription">Créer un compte maintenant</a>
    </div>
</div>