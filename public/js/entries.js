function toggleDay(date) {
    const content = document.getElementById('content-' + date);
    const icon = document.getElementById('icon-' + date);

    if (content.classList.contains('collapsed')) {
        content.classList.remove('collapsed');
        icon.textContent = '▼';
    } else {
        content.classList.add('collapsed');
        icon.textContent = '▶';
    }
}
