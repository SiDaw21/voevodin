// Подсветка звёзд при наведении
document.addEventListener('DOMContentLoaded', function() {
    const starContainers = document.querySelectorAll('.stars');
    starContainers.forEach(container => {
        const labels = container.querySelectorAll('label');
        labels.forEach((label, index) => {
            label.addEventListener('mouseenter', () => {
                labels.forEach((l, i) => {
                    l.style.color = i <= index ? '#f1c40f' : 'rgba(255,255,255,0.3)';
                });
            });
        });
        container.addEventListener('mouseleave', () => {
            const checked = container.querySelector('input:checked');
            const checkedIndex = checked ? parseInt(checked.value) - 1 : -1;
            labels.forEach((l, i) => {
                l.style.color = i <= checkedIndex ? '#f1c40f' : 'rgba(255,255,255,0.3)';
            });
        });
    });

    // Плавное появление карточек
    const cards = document.querySelectorAll('.exhibit-card, .card');
    cards.forEach((card, i) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.5s';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, i * 100);
    });
});