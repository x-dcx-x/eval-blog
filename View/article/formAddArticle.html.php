<form action="/index.php?c=article&a=add-article" method="post" enctype="multipart/form-data">
    <label for="img">Image de couverture : </label>
    <input type="file" name="img" id="img" accept=".jpg, .jpeg, .png">

    <label for="title">Titre : </label>
    <input type="text" name="title" id="title" required>

    <label for="content">Contenu : </label>
    <textarea name="content" id="content" cols="30" rows="10"></textarea>

    <input type="submit" name="submit" value="Publier" class="button">
</form>