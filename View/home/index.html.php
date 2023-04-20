<h1>home</h1>
<h2>Nos articles</h2>
    <?php
        foreach ($data['article'] as $article) { ?>
            <article>
                <a href="/index.php?c=article&a=displayArticle&id=<?= $article->getId() ?>">
                    <div>
                        <img src="/uploads/<?= $article->getImage() ?>" alt="Image de couverture de l'article"
                             class="artImage">
                    </div>
                    <div>
                        <p class="artTitle"><?= $article->getTitle() ?></p>
                    </div>
                </a>
            </article>
        <?php    }
    ?>