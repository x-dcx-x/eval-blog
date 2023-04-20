<div>
    <h1 id="titleArticle"><?=$data['article']->getTitle()?></h1>
    <img src="/uploads/<?=$data['article']->getImage()?>" alt="">
    <p id="contentArticle"><?=$data['article']->getContent()?></p>
    <p id="infoArticle">
        Article posté par
        <?=$data['article']->getUser()->getUsername()?>
    </p>
</div>
