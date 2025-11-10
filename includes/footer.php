    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- About -->
                <div>
                    <h3 class="text-lg font-bold mb-4"><?php echo APP_NAME; ?></h3>
                    <p class="text-gray-400 text-sm">
                        Find your perfect home away from home. Quality properties for travelers worldwide in Ghana.
                    </p>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-lg font-bold mb-4">Quick Links</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="/public/index.php" class="text-gray-400 hover:text-white">Properties</a></li>
                        <?php if (!Auth::check()): ?>
                            <li><a href="/public/login.php" class="text-gray-400 hover:text-white">Login</a></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- For Property Managers -->
                <div>
                    <h3 class="text-lg font-bold mb-4">For Property Managers</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="/public/list-property.php" class="text-gray-400 hover:text-white">Become a Manager</a></li>
                        <?php if (Auth::check() && Auth::hasRole('admin')): ?>
                            <li><a href="/admin/index.php" class="text-gray-400 hover:text-white">Manager Dashboard</a></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Connect With Us -->
                <div>
                    <h3 class="text-lg font-bold mb-4">Connect With Us</h3>
                    <div class="flex space-x-4">
                        <a href="<?php echo getWhatsAppLink(); ?>" target="_blank" class="text-gray-400 hover:text-white text-2xl">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="<?php echo getInstagramLink(); ?>" target="_blank" class="text-gray-400 hover:text-white text-2xl">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="<?php echo getLinkedInLink(); ?>" target="_blank" class="text-gray-400 hover:text-white text-2xl">
                            <i class="fab fa-linkedin"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400 text-sm">
                <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Mobile menu toggle script -->
    <script>
        document.getElementById('mobile-menu-button')?.addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });
    </script>

    <script src="/assets/js/app.js"></script>
</body>
</html>
