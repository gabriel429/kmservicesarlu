// Effet sticky navbar au scroll
let lastScrollTop = 0;
const header = document.querySelector('.site-header');
const scrollThreshold = 50;

window.addEventListener('scroll', function() {
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    
    if (scrollTop > scrollThreshold) {
        header.classList.add('scrolled');
    } else {
        header.classList.remove('scrolled');
    }
    
    lastScrollTop = scrollTop;
});

document.addEventListener('DOMContentLoaded', () => {
    const promoVideos = document.querySelectorAll('.video-card video');
    promoVideos.forEach((video) => {
        const card = video.closest('.video-card');
        const playOverlay = card ? card.querySelector('.video-play') : null;

        if (!playOverlay) {
            return;
        }

        const hideOverlay = () => playOverlay.classList.add('is-hidden');
        const showOverlay = () => playOverlay.classList.remove('is-hidden');

        video.addEventListener('play', hideOverlay);
        video.addEventListener('pause', showOverlay);
        video.addEventListener('ended', showOverlay);

        playOverlay.addEventListener('click', () => {
            video.play();
        });
    });

    const orderButtons = document.querySelectorAll('[data-order]');
    const orderModal = document.getElementById('orderModal');

    if (orderModal) {
        const modal = new bootstrap.Modal(orderModal);
        const productNameInput = orderModal.querySelector('#productName');
        const productIdInput = orderModal.querySelector('#productId');
        const orderForm = orderModal.querySelector('#orderForm');

        orderButtons.forEach((btn) => {
            btn.addEventListener('click', () => {
                productNameInput.value = btn.dataset.productName;
                productIdInput.value = btn.dataset.productId;
                modal.show();
            });
        });

        orderForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const payload = {
                product_id: productIdInput.value,
                product_name: productNameInput.value,
                nom: orderForm.nom.value,
                telephone: orderForm.telephone.value,
                quantite: orderForm.quantite.value,
                message: orderForm.message.value
            };

            try {
                const response = await fetch('api/create_order.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();

                if (result.success) {
                    const whatsappMessage = encodeURIComponent(result.whatsapp_message);
                    window.open(`https://wa.me/${result.whatsapp_number}?text=${whatsappMessage}`, '_blank');
                    orderForm.reset();
                    modal.hide();
                } else {
                    alert(result.message || 'Impossible de créer la commande.');
                }
            } catch (error) {
                alert('Erreur réseau.');
            }
        });
    }

    const stepButtons = document.querySelectorAll('[data-step]');
    if (stepButtons.length) {
        stepButtons.forEach((btn) => {
            btn.addEventListener('click', () => {
                const target = btn.dataset.step;
                document.querySelectorAll('.form-step').forEach((step) => step.classList.remove('active'));
                document.querySelector(`.form-step[data-step="${target}"]`).classList.add('active');
            });
        });
    }
});
