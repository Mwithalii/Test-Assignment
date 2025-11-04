/child-theme/
  style.css             # Child theme descriptor — sets Template: to my parent theme folder
  functions.php         # Enqueues the parent style and requires cpt-registration.php
  single-book.php       # Single template for Book CPT (shows Title, Author, ISBN, Price + "But with Stripe" button)

cpt-registration.php    # Registers the 'book' CPT (register_post_type)

plugins/book-importer-free/
  book-importer-free.php   # Small plugin that maps book to product and appends the Buy button

