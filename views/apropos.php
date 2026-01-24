<!-- Page À Propos -->
<section class="about-page">
    <div class="container">
        <h1>À Propos de KM Services</h1>
        
        <div class="about-content">
            <div class="about-text">
                <h2>Notre Histoire</h2>
                <p>Créée depuis 2020, notre société dont le siège social est située sise l'avenue Kabalo N°235, Quartier Makutano, dans la com­mune de Lubumbashi est en règle avec la législation congolaise en matière de création d'entreprise. Enregistrée sous le numéro RCCM : CD/LS­ H/RCCM/20-8-00393, enrègle avec l'Autorité de Régulation de la Sous-trai­ tance dans le Secteur Privé (ARSP), ayant comme numéro impôt : A2037918N ; Identification nationale 05-F4200-N62254T ; CNSS : 1014658600	;	ONEME 041946A20 INPP 08475,80
                 <br>L'entreprise travaille particuliers	aux grandes entreprises en passant par les insti­tutions publiques. KM SERVICES a également établi des partenaiats stratégiques avec des entreprises	du secteur pour offrir des solutions intégrées et de haute	qualité    
            </p>           
                <h2>Notre Mission</h2>
                <p>Offrir des solutions complètes en construction et forage avec expertise, professionnalisme et intégrité. Nous nous efforçons de délivrer des projets à temps, dans le budget, et conformes aux normes de qualité les plus élevées.
                   
                </p>
                
                <h2>Nos Valeurs</h2>
                <ul class="values-list">
                    <li><strong>Excellence:</strong> Qualité supérieure dans tous nos services</li>
                    <li><strong>Intégrité:</strong> Transparence et honnêteté en toute transaction</li>
                    <li><strong>Innovation:</strong> Adoption de technologies modernes</li>
                    <li><strong>Responsabilité:</strong> Engagement envers nos clients et communautés</li>
                    <li><strong>Durabilité:</strong> Pratiques respectueuses de l'environnement</li>
                </ul>
            </div>
            
            <div class="about-stats">
                <div class="stat-card">
                    <h3>30+</h3>
                    <p>Projets Réalisés</p>
                </div>
                <div class="stat-card">
                    <h3>5+</h3>
                    <p>Années d'Expérience</p>
                </div>
                <div class="stat-card">
                    <h3>100%</h3>
                    <p>Clients Satisfaits</p>
                </div>
                <div class="stat-card">
                    <h3>500+</h3>
                    <p>Produits en Stock</p>
                </div>
            </div>
        </div>
        
        <div class="team-section">
            <h2>Notre Équipe</h2>
            <p>Une équipe dévouée et compétente mettant son expertise à votre service.</p>
            <div class="team-grid">
                <div class="team-member">
                    <div class="member-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>Directeur Général</h3>
                    <p>KONGOLO DELPHIN</p>
                    <p>Gestion générale et stratégie</p>
                </div>
                <div class="team-member">
                    <div class="member-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>Chef de Projet</h3>
                    <p>Supervision des projets</p>
                </div>
                <div class="team-member">
                    <div class="member-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>Technicien Forage</h3>
                    <p>Opérations de forage</p>
                </div>
                <div class="team-member">
                    <div class="member-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>Responsable Boutique</h3>
                    <p>Gestion des matériels</p>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.about-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
    margin-bottom: 3rem;
}

.about-text h2 {
    text-align: left;
    margin-top: 1.5rem;
    margin-bottom: 1rem;
}

.about-text p {
    color: var(--text-secondary);
    margin-bottom: 1rem;
    line-height: 1.8;
}

.values-list {
    list-style: none;
}

.values-list li {
    padding: 0.75rem 0;
    padding-left: 1.5rem;
    position: relative;
    color: var(--text-secondary);
}

.values-list li:before {
    content: "✓";
    position: absolute;
    left: 0;
    color: var(--accent-color);
    font-weight: bold;
}

.about-stats {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
}

.stat-card {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    padding: 2rem;
    border-radius: 0.5rem;
    text-align: center;
}

.stat-card h3 {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
    color: white;
}

.stat-card p {
    font-size: 1rem;
}

.team-section {
    background-color: var(--light-bg);
    padding: 3rem 2rem;
    border-radius: 0.5rem;
    text-align: center;
}

.team-section h2 {
    text-align: center;
}

.team-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.team-member {
    background: white;
    padding: 2rem;
    border-radius: 0.5rem;
    box-shadow: var(--shadow);
}

.member-avatar {
    font-size: 3rem;
    color: var(--secondary-color);
    margin-bottom: 1rem;
}

.team-member h3 {
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.team-member p {
    color: var(--text-secondary);
}

@media (max-width: 768px) {
    .about-content {
        grid-template-columns: 1fr;
    }
}
</style>
