<b>/child-theme/</b><br>
  <p>style.css             # Child theme descriptor — sets Template: to my parent theme folder</p> <br>
  
  <p>functions.php         # Enqueues the parent style and requires cpt-registration.php</p> <br>
  
  <p>single-book.php       # Single template for Book CPT (shows Title, Author, ISBN, Price + "Buy with Stripe" button)</p> <br>
<br>

<b>/child-theme/library</b><br>
<p>acf-fields.php       # adds the advanced custom Fields: Title, Author, ISBN, Price</p> <br>

<p>cpt.php    # Registers the 'book' CPT (register_post_type)</p> <br>

<br>

<b>plugin: book-importer-free/</b><br>
  <p>book-importer-free.php   # Small plugin that maps book to product and appends the Buy button</p> 
