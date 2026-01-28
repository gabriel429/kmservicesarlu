<?php
// Traitement des formulaires de contact
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        require_once dirname(__DIR__) . '/config/config.php';
        require_once dirname(__DIR__) . '/app/MySQL.php';
        
        // Assurer que la table existe
        try {
            MySQLCore::execute(
                "CREATE TABLE IF NOT EXISTS contacts (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    nom VARCHAR(150) NOT NULL,
                    email VARCHAR(150) NOT NULL,
                    telephone VARCHAR(20),
                    sujet VARCHAR(200),
                    message LONGTEXT NOT NULL,
                    statut ENUM('nouveau', 'traite', 'archive') DEFAULT 'nouveau',
                    lu TINYINT DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_statut (statut),
                    INDEX idx_created_at (created_at)
                )"
            );
        } catch (Throwable $te) {
            // La table existe déjà ou erreur - continuer
        }
        
        $nom = trim($_POST['nom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telephone = trim($_POST['telephone'] ?? '');
        $sujet = trim($_POST['sujet'] ?? '');
        $message = trim($_POST['message'] ?? '');
        
        if ($nom && $email && $sujet && $message) {
            // Enregistrer le message en base de données
            $result = MySQLCore::execute(
                "INSERT INTO contacts (nom, email, telephone, sujet, message, statut) 
                 VALUES (?, ?, ?, ?, ?, 'nouveau')",
                [$nom, $email, $telephone, $sujet, $message]
            );
            
            if ($result) {
                // Envoyer un email de confirmation au client
                $subject = 'Confirmation de réception de votre message - KM Services';
                $body = "Bonjour $nom,\n\n";
                $body .= "Nous avons bien reçu votre message du " . date('d/m/Y à H:i') . ".\n\n";
                $body .= "Nous traiterons votre demande et vous répondrons dans les meilleurs délais.\n\n";
                $body .= "Cordialement,\n";
                $body .= "L'équipe KM Services\n";
                $body .= "contact@kmservicesarlu.cd\n";
                
                $headers = "From: contact@kmservicesarlu.cd\r\n";
                $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
                $headers .= "Reply-To: contact@kmservicesarlu.cd\r\n";
                
                @mail($email, $subject, $body, $headers);
                
                // Rediriger avec message de succès
                header('Location: ' . APP_URL . 'contact?success=1');
                exit;
            } else {
                error_log('Failed to insert contact message');
            }
        }
    } catch (Throwable $e) {
        error_log('Error in contact form: ' . $e->getMessage());
    }
}
?>
<!-- Page Contact -->
<section class="contact-page">
    <div class="container">
        <h1>Nous Contacter</h1>
        
        <div class="contact-content">
            <div class="contact-form-section">
                <h2>Formulaire de Contact</h2>
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> Votre message a été envoyé avec succès!
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="?page=contact&service=general" class="contact-form">
                    <div class="form-group">
                        <label for="nom">Nom *</label>
                        <input type="text" id="nom" name="nom" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="telephone">Téléphone</label>
                        <input type="tel" id="telephone" name="telephone">
                    </div>
                    
                    <div class="form-group">
                        <label for="sujet">Sujet *</label>
                        <input type="text" id="sujet" name="sujet" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Message *</label>
                        <textarea id="message" name="message" rows="6" required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Envoyer</button>
                </form>
                
                <div style="margin-top: 2rem; padding: 1.5rem; background-color: #f0f8ff; border-left: 4px solid #17a2b8; border-radius: 4px;">
                    <p style="margin: 0;"><strong>Besoin d'un devis ?</strong> Consultez notre <a href="<?php echo APP_URL; ?>devis" style="color: #17a2b8; text-decoration: none; font-weight: bold;">page de devis</a> pour demander un devis détaillé pour vos projets.</p>
                </div>
            </div>
            
            <div class="contact-info-section">
                <h2>Nos Coordonnées</h2>
                
                <div class="info-block">
                    <i class="fas fa-map-marker-alt"></i>
                    <div>
                        <h3>Adresse</h3>
                        <p>235, Kabalo, Q/Makutano, C/Lubumbashi,Haut-Katanga Rép. Dém. du Congo</p>
                    </div>
                </div>
                
                <div class="info-block">
                    <i class="fas fa-phone"></i>
                    <div>
                        <h3>Téléphone</h3>
                        <p>+243 (0) 892 017 793/+243 (0) 999 920 715</p>
                    </div>
                </div>
                
                <div class="info-block">
                    <i class="fas fa-envelope"></i>
                    <div>
                        <h3>Email</h3>
                        <p><a href="mailto:contact@kmservicesarlu.cd">contact@kmservicesarlu.cd</a></p>
                    </div>
                </div>
                
                <div class="info-block">
                    <i class="fas fa-clock"></i>
                    <div>
                        <h3>Horaires</h3>
                        <p>Lundi - Vendredi: 8h00 - 18h00</p>
                        <p>Samedi: 9h00 - 14h00</p>
                    </div>
                </div>
                
                <div class="map-container">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3907.4869268421676!2d27.471743373806735!3d-11.659863434815195!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x19723eb9e44ca7db%3A0xfb57edc30bc6e0a6!2sAv.%20de%20Kabalo%2C%20Lubumbashi!5e0!3m2!1sfr!2scd!4v1769096449066!5m2!1sfr!2scd" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
        </div>
    </div>
</section>
