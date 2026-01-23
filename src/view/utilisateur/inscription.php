<div class="form-card">
    <h2>Créer un compte</h2>
    
    <?php if (isset($error)): ?>
        <div class="alert-error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="frontController.php">
        
        <div class="form-group">
            <label>Prénom</label>
            <input type="text" name="Prenom" required value="<?= isset($_POST['Prenom']) ? htmlspecialchars($_POST['Prenom']) : '' ?>">
        </div>

        <div class="form-group">
            <label>Nom</label>
            <input type="text" name="nom" required value="<?= isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : '' ?>">
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
        </div>

        <div class="form-group">
            <label>Mot de passe (Min. 6 caractères)</label>
            <input type="password" name="mdp" required>
        </div>

        <div class="form-group">
            <label>Confirmation</label>
            <input type="password" name="Cmdp" required>
        </div>

        <div class="form-group">
            <label>Profil</label>
            <select name="ProfilUtilisateur">
                <option value="GrandPublic">Grand Public</option>
                <option value="Etudiant">Etudiant</option>
                <option value="Chercheur">Chercheur</option>
            </select>
        </div>

        <input type="hidden" name="controller" value="utilisateur">
        <input type="hidden" name="action" value="created">

        <button type="submit" class="btn-submit">
            S'inscrire
        </button>
    </form>
    
    <div class="form-footer">
        <a href="frontController.php?controller=utilisateur&action=connexion">Déjà un compte ? Connectez-vous</a>
    </div>
</div>