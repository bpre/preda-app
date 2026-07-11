<script>
    document.addEventListener('DOMContentLoaded', function() {
        window.addEventListener('registerKey', event => {

            window.addEventListener('keydown', e => {

                if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'd') {
                    e.preventDefault();
                    if(event.detail[0] != '')
                    {
                        window.open(event.detail[0], '_blank', 'noopener,noreferrer');
                    }

                }
            });
        });
    });
</script>
