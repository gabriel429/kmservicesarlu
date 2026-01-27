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
                $body .= "contact@kmservices.cd\n";
                
                $headers = "From: contact@kmservices.cd\r\n";
                $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
                $headers .= "Reply-To: contact@kmservices.cd\r\n";
                
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

                <hr>
                
                <h2 style="margin-top: 40px;">Demande de Devis</h2>
                
                <div class="form-group" style="margin-bottom: 30px;">
                    <label for="service-selector" style="font-size: 16px; font-weight: bold;">Sélectionnez le service *</label>
                    <select id="service-selector" style="width: 100%; padding: 10px; font-size: 14px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="">-- Choisir un service --</option>
                        <option value="forage">Forage</option>
                        <option value="plomberie">Plomberie</option>
                        <option value="peinture">Peinture</option>
                        <option value="electrification">Électrification</option>
                        <option value="construction">Construction</option>
                        <option value="materiels">Matériels de Construction</option>
                    </select>
                </div>
                
                <!-- Formulaires cachés par défaut -->
                <div id="forage-form" class="service-form" style="display: none;">
                <form method="POST" action="?page=contact&service=forage" enctype="multipart/form-data" class="contact-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="drilling_nom">Nom *</label>
                            <input type="text" id="drilling_nom" name="nom" required>
                        </div>
                        <div class="form-group">
                            <label for="drilling_email">Email *</label>
                            <input type="email" id="drilling_email" name="email" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="drilling_tel">Téléphone *</label>
                            <input type="tel" id="drilling_tel" name="telephone" required>
                        </div>
                        <div class="form-group">
                            <label for="drilling_type">Type de Forage *</label>
                            <select id="drilling_type" name="type_forage" required>
                                <option value="">Choisir un type</option>
                                <option value="puits">Puits d'eau</option>
                                <option value="geotechnique">Géotechnique</option>
                                <option value="exploration">Exploration</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="drilling_profondeur">Profondeur Estimée (mètres)</label>
                        <input type="number" id="drilling_profondeur" name="profondeur_estimee">
                    </div>
                    
                    <div class="form-group">
                        <label for="drilling_localisation">Localisation *</label>
                        <input type="text" id="drilling_localisation" name="localisation" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="drilling_desc">Description du Projet</label>
                        <textarea id="drilling_desc" name="description" rows="4"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="drilling_delai">Délai Souhaité *</label>
                        <select id="drilling_delai" name="delai_souhaite" required>
                            <option value="">Choisir un délai</option>
                            <option value="urgent">Urgent (moins d'une semaine)</option>
                            <option value="court">Court terme (1-2 semaines)</option>
                            <option value="moyen">Moyen terme (1 mois)</option>
                            <option value="flexible">Flexible</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="drilling_budget">Budget Estimé (USD)</label>
                        <input type="number" id="drilling_budget" name="budget_estime" step="0.01">
                    </div>
                    
                    <div class="form-group">
                        <label for="drilling_doc">Document Joint (optionnel)</label>
                        <input type="file" id="drilling_doc" name="document_joint" accept=".pdf,.doc,.docx,.xlsx">
                        <small>Formats acceptés: PDF, DOC, DOCX, XLSX</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Demander un Devis</button>
                </form>
                </div>
                
                <div id="plomberie-form" class="service-form" style="display: none;">
                <form method="POST" action="?page=contact&service=plomberie" enctype="multipart/form-data" class="contact-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="plumbing_nom">Nom *</label>
                            <input type="text" id="plumbing_nom" name="nom" required>
                        </div>
                        <div class="form-group">
                            <label for="plumbing_email">Email *</label>
                            <input type="email" id="plumbing_email" name="email" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="plumbing_tel">Téléphone *</label>
                            <input type="tel" id="plumbing_tel" name="telephone" required>
                        </div>
                        <div class="form-group">
                            <label for="plumbing_type">Type de Travail *</label>
                            <select id="plumbing_type" name="type_travail" required>
                                <option value="">Choisir un type</option>
                                <option value="installation">Installation</option>
                                <option value="reparation">Réparation</option>
                                <option value="entretien">Entretien</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="plumbing_desc">Description du Travail *</label>
                        <textarea id="plumbing_desc" name="description" rows="4" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="plumbing_localisation">Localisation *</label>
                        <input type="text" id="plumbing_localisation" name="localisation" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="plumbing_delai">Délai Souhaité *</label>
                        <select id="plumbing_delai" name="delai_souhaite" required>
                            <option value="">Choisir un délai</option>
                            <option value="urgent">Urgent (moins d'une semaine)</option>
                            <option value="court">Court terme (1-2 semaines)</option>
                            <option value="moyen">Moyen terme (1 mois)</option>
                            <option value="flexible">Flexible</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="plumbing_budget">Budget Estimé (USD)</label>
                        <input type="number" id="plumbing_budget" name="budget_estime" step="0.01">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Demander un Devis</button>
                </form>
                </div>
                
                <div id="peinture-form" class="service-form" style="display: none;">
                <form method="POST" action="?page=contact&service=peinture" enctype="multipart/form-data" class="contact-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="painting_nom">Nom *</label>
                            <input type="text" id="painting_nom" name="nom" required>
                        </div>
                        <div class="form-group">
                            <label for="painting_email">Email *</label>
                            <input type="email" id="painting_email" name="email" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="painting_tel">Téléphone *</label>
                            <input type="tel" id="painting_tel" name="telephone" required>
                        </div>
                        <div class="form-group">
                            <label for="painting_type">Type de Peinture *</label>
                            <select id="painting_type" name="type_peinture" required>
                                <option value="">Choisir un type</option>
                                <option value="interieur">Peinture Intérieure</option>
                                <option value="exterieur">Peinture Extérieure</option>
                                <option value="decorative">Finitions Décoratives</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="painting_surface">Surface à Peindre (m²)</label>
                            <input type="number" id="painting_surface" name="surface" step="0.01">
                        </div>
                        <div class="form-group">
                            <label for="painting_color">Couleur Souhaitée</label>
                            <input type="text" id="painting_color" name="couleur">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="painting_desc">Description du Projet</label>
                        <textarea id="painting_desc" name="description" rows="4"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="painting_localisation">Localisation *</label>
                        <input type="text" id="painting_localisation" name="localisation" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="painting_delai">Délai Souhaité *</label>
                        <select id="painting_delai" name="delai_souhaite" required>
                            <option value="">Choisir un délai</option>
                            <option value="urgent">Urgent (moins d'une semaine)</option>
                            <option value="court">Court terme (1-2 semaines)</option>
                            <option value="moyen">Moyen terme (1 mois)</option>
                            <option value="flexible">Flexible</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="painting_budget">Budget Estimé (USD)</label>
                        <input type="number" id="painting_budget" name="budget_estime" step="0.01">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Demander un Devis</button>
                </form>
                </div>
                
                <div id="electrification-form" class="service-form" style="display: none;">
                <form method="POST" action="?page=contact&service=electrification" enctype="multipart/form-data" class="contact-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="electric_nom">Nom *</label>
                            <input type="text" id="electric_nom" name="nom" required>
                        </div>
                        <div class="form-group">
                            <label for="electric_email">Email *</label>
                            <input type="email" id="electric_email" name="email" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="electric_tel">Téléphone *</label>
                            <input type="tel" id="electric_tel" name="telephone" required>
                        </div>
                        <div class="form-group">
                            <label for="electric_type">Type de Travail *</label>
                            <select id="electric_type" name="type_travail" required>
                                <option value="">Choisir un type</option>
                                <option value="installation">Installation Neuve</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="upgrade">Upgrade Système</option>
                                <option value="reparation">Réparation</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="electric_desc">Description du Projet *</label>
                        <textarea id="electric_desc" name="description" rows="4" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="electric_localisation">Localisation *</label>
                        <input type="text" id="electric_localisation" name="localisation" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="electric_delai">Délai Souhaité *</label>
                        <select id="electric_delai" name="delai_souhaite" required>
                            <option value="">Choisir un délai</option>
                            <option value="urgent">Urgent (moins d'une semaine)</option>
                            <option value="court">Court terme (1-2 semaines)</option>
                            <option value="moyen">Moyen terme (1 mois)</option>
                            <option value="flexible">Flexible</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="electric_budget">Budget Estimé (USD)</label>
                        <input type="number" id="electric_budget" name="budget_estime" step="0.01">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Demander un Devis</button>
                </form>
                </div>
                
                <div id="construction-form" class="service-form" style="display: none;">
                <form method="POST" action="?page=contact&service=construction" enctype="multipart/form-data" class="contact-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="construction_nom">Nom *</label>
                            <input type="text" id="construction_nom" name="nom" required>
                        </div>
                        <div class="form-group">
                            <label for="construction_email">Email *</label>
                            <input type="email" id="construction_email" name="email" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="construction_tel">Téléphone *</label>
                            <input type="tel" id="construction_tel" name="telephone" required>
                        </div>
                        <div class="form-group">
                            <label for="construction_type">Type de Projet *</label>
                            <select id="construction_type" name="type_projet" required>
                                <option value="">Choisir un type</option>
                                <option value="commercial">Bâtiments Commerciaux</option>
                                <option value="infrastructure">Infrastructure</option>
                                <option value="renovation">Rénovation</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="construction_desc">Description du Projet *</label>
                        <textarea id="construction_desc" name="description" rows="4" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="construction_localisation">Localisation *</label>
                        <input type="text" id="construction_localisation" name="localisation" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="construction_surface">Surface Estimée (m²)</label>
                        <input type="number" id="construction_surface" name="surface" step="0.01">
                    </div>
                    
                    <div class="form-group">
                        <label for="construction_delai">Délai Souhaité *</label>
                        <select id="construction_delai" name="delai_souhaite" required>
                            <option value="">Choisir un délai</option>
                            <option value="urgent">Urgent (moins d'une semaine)</option>
                            <option value="court">Court terme (1-2 semaines)</option>
                            <option value="moyen">Moyen terme (1 mois)</option>
                            <option value="long">Long terme (3+ mois)</option>
                            <option value="flexible">Flexible</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="construction_budget">Budget Estimé (USD)</label>
                        <input type="number" id="construction_budget" name="budget_estime" step="0.01">
                    </div>
                    
                    <div class="form-group">
                        <label for="construction_doc">Document Joint (optionnel)</label>
                        <input type="file" id="construction_doc" name="document_joint" accept=".pdf,.doc,.docx,.xlsx">
                        <small>Formats acceptés: PDF, DOC, DOCX, XLSX</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Demander un Devis</button>
                </form>
                </div>
                
                <div id="materiels-form" class="service-form" style="display: none;">
                <form method="POST" action="?page=contact&service=materiels" enctype="multipart/form-data" class="contact-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="materials_nom">Nom *</label>
                            <input type="text" id="materials_nom" name="nom" required>
                        </div>
                        <div class="form-group">
                            <label for="materials_email">Email *</label>
                            <input type="email" id="materials_email" name="email" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="materials_tel">Téléphone *</label>
                            <input type="tel" id="materials_tel" name="telephone" required>
                        </div>
                        <div class="form-group">
                            <label for="materials_type">Type de Matériel *</label>
                            <select id="materials_type" name="type_materiel" required>
                                <option value="">Choisir un type</option>
                                <option value="ciments">Ciments</option>
                                <option value="outils">Outils</option>
                                <option value="bois">Bois</option>
                                <option value="metaux">Métaux</option>
                                <option value="isolants">Isolants</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="materials_desc">Liste des Matériels Demandés *</label>
                        <textarea id="materials_desc" name="description" rows="4" required placeholder="Décrivez les matériels que vous cherchez..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="materials_quantite">Quantité Estimée</label>
                        <input type="text" id="materials_quantite" name="quantite">
                    </div>
                    
                    <div class="form-group">
                        <label for="materials_delai">Délai de Livraison Souhaité *</label>
                        <select id="materials_delai" name="delai_souhaite" required>
                            <option value="">Choisir un délai</option>
                            <option value="immediatement">Immédiatement</option>
                            <option value="court">Court terme (1-2 semaines)</option>
                            <option value="moyen">Moyen terme (1 mois)</option>
                            <option value="flexible">Flexible</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="materials_budget">Budget Estimé (USD)</label>
                        <input type="number" id="materials_budget" name="budget_estime" step="0.01">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Demander un Devis</button>
                </form>
                </div>
                
                <script>
                    document.getElementById('service-selector').addEventListener('change', function() {
                        const selectedService = this.value;
                        
                        // Masquer tous les formulaires
                        document.querySelectorAll('.service-form').forEach(form => {
                            form.style.display = 'none';
                        });
                        
                        // Afficher le formulaire sélectionné
                        if (selectedService) {
                            const formElement = document.getElementById(selectedService + '-form');
                            if (formElement) {
                                formElement.style.display = 'block';
                                // Scroll vers le formulaire
                                formElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
                            }
                        }
                    });
                </script>
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
                        <p><a href="mailto:contact@kmservices.cd">contact@kmservices.cd</a></p>
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
