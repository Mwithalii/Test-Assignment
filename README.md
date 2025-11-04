📁 Project Structure

child-theme/
├── 📄 style.css
│   └── Child theme descriptor — sets Template to parent theme folder
├── 📄 functions.php
│   └── Enqueues parent styles and requires cpt-registration.php
├── 📄 single-book.php
│   └── Single template for Book CPT
│       • Displays: Title, Author, ISBN, Price
│       • Includes "Buy with Stripe" button

cpt-registration.php
└── Registers the 'book' Custom Post Type using register_post_type()

plugins/book-importer-free/
└── 📄 book-importer-free.php
    └── Maps books to products and appends Buy button functionality
