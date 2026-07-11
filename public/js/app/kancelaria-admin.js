window.addEventListener('copy-to-clipboard', (event) => {
    navigator.clipboard.writeText(event.detail);
})
