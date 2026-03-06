<div id="carouselExampleAutoplaying" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-inner">
        <?php
        $first = true;
        foreach ($articles as $article) { ?>
            <div class="carousel-item <?= $first ? 'active' : '' ?>" style="background-color: #90b380;">
                <a href="<?= $article['lien'] ?>">
                    <h3 style="color: white; background-color: #689255;"><?= $article['titre'] ?></h3>
                    <img src="<?= $article['image'] ?>" style="max-width: 100%; height: auto;">
                    <p><?= $article['description'] ?></p>
                    <p style="text-align: center;"><?= $article['date'] ?></p>
                </a>
            </div>
        <?php
            $first = false;
        } ?>
    </div>
    <button class="carousel-control-prev"  type="button" data-bs-target="#carouselExampleAutoplaying" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" style="margin-top: 500px;" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleAutoplaying" data-bs-slide="next">
        <span class="carousel-control-next-icon" style="margin-top: 500px;" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
    </button>
</div>