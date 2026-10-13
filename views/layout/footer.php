        </section>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    lucide.createIcons();
    document.querySelectorAll('[data-modal-target]').forEach((button) => {
        button.addEventListener('click', () => document.querySelector(button.dataset.modalTarget)?.classList.remove('d-none'));
    });
    document.querySelectorAll('[data-modal-close]').forEach((button) => {
        button.addEventListener('click', () => button.closest('[data-modal]')?.classList.add('d-none'));
    });
</script>
</body>
</html>
