<div style="max-width: 500px; margin: 20px auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
    <h2 style="text-align: center;">Inscription</h2>
    
    <?php if (isset($error)): ?>
        <div style="color: white; background-color: #dc3545; padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="frontController.php">
        <fieldset style="border:none; padding:0;">
            
            <label>Prénom :</label>
            <input type="text" name="Prenom" required style="width: 100%; padding: 8px; margin-bottom: 10px;">

            <label>Nom :</label>
            <input type="text" name="nom" required style="width: 100%; padding: 8px; margin-bottom: 10px;">

            <label>Email :</label>
            <input type="email" name="email" required style="width: 100%; padding: 8px; margin-bottom: 10px;">

            <label>Mot de passe :</label>
            <input type="password" name="mdp" required style="width: 100%; padding: 8px; margin-bottom: 10px;">

            <label>Confirmation :</label>
            <input type="password" name="Cmdp" required style="width: 100%; padding: 8px; margin-bottom: 10px;">

            <label>Profil :</label>
            <select name="ProfilUtilisateur" style="width: 100%; padding: 8px; margin-bottom: 20px;">
                <option value="GrandPublic">Grand Public</option>
                <option value="Etudiant">Etudiant</option>
                <option value="Chercheur">Chercheur</option>
            </select>

            <input type="hidden" name="controller" value="utilisateur">
            <input type="hidden" name="action" value="created">

            <button type="submit" style="width: 100%; padding: 10px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
                S'inscrire
            </button>
        </fieldset>
    </form>
    
    <p style="text-align: center; margin-top: 15px;">
        <a href="frontController.php?controller=utilisateur&action=connexion">Déjà un compte ?</a>
    </p>
</div>