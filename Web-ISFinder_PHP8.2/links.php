<!DOCTYPE html>
<html>
<head>
    <title>Links</title>
    <meta charset="utf-8" /> 
    <meta name="author" content="Jo" />
    <meta name="keywords" content="Links" />
    <link type="text/css" rel="stylesheet" href="styles/styles.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="styles/links.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="styles/menu.css" media="screen" />
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
</head>
<body>
<div id="page">
    <header>
    </header>

    <?php 
    $nav_en_cours = 'infos';
    include_once('include/menu.inc.php');
    ?>

    <article>
        <h2>Favourite Links</h2>
        <hr/>

        <!-- ============================================= -->
        <!-- Category: Sequencing Centers (5 links)        -->
        <!-- ============================================= -->
        <section class="links-category">
            <h3 class="links-category-title">Sequencing Centers</h3>
            <div class="links-row">
                <div class="links-carousel">
                    <div class="links-track">
                        <!-- Card 1: JCVI -->
                        <a href="http://www.jcvi.org/cms/home/" target="_blank" class="link-card">
                            <div class="link-card-img">
                                <img src="images/logo_Venter.png" alt="Craig Venter Institute">
                            </div>
                            <span class="link-card-name">JCVI (ex. TIGR)</span>
                        </a>
                        <!-- Card 2: E. Coli Genome Project -->
                        <a href="http://www.genome.wisc.edu/" target="_blank" class="link-card" title="E. Coli Genome Project">
                            <div class="link-card-img">
                                <img src="images/logo_EColiGenome.png" alt="E. Coli Genome Project">
                            </div>
                            <span class="link-card-name">E. Coli Genome Project</span>
                        </a>
                        <!-- Card 3: Sanger Institute -->
                        <a href="https://www.sanger.ac.uk/" target="_blank" class="link-card">
                            <div class="link-card-img">
                                <img src="images/logo_Sanger.png" alt="Sanger Institute">
                            </div>
                            <span class="link-card-name">Sanger Institute</span>
                        </a>
                        <!-- Card 4: Genoscope -->
                        <a href="http://www.genoscope.cns.fr/spip/" target="_blank" class="link-card">
                            <div class="link-card-img">
                                <img src="images/logo_Genoscope.png" alt="CEA-Genoscope">
                            </div>
                            <span class="link-card-name">CEA-Genoscope</span>
                        </a>
                        <!-- Card 5: JGI -->
                        <a href="http://jgi.doe.gov/" target="_blank" class="link-card">
                            <div class="link-card-img">
                                <img src="images/logo_JGI.png" alt="Joint Genome Institute">
                            </div>
                            <span class="link-card-name">Joint Genome Institute</span>
                        </a>
                    </div>
                </div>
                <button class="links-see-more" aria-label="Show more Sequencing Centers links" title="Show more">&#10132;</button>
            </div>
        </section>

        <!-- ============================================= -->
        <!-- Category: Databases (10 links)                -->
        <!-- ============================================= -->
        <section class="links-category">
            <h3 class="links-category-title">Databases</h3>
            <div class="links-row">
                <div class="links-carousel">
                    <div class="links-track">
                        <!-- Card 1: ICEberg -->
                        <a href="http://db-mml.sjtu.edu.cn/ICEberg/index.php" target="_blank" class="link-card">
                            <div class="link-card-img">
                                <img src="images/logo_iceberg.png" alt="ICEberg">
                            </div>
                            <span class="link-card-name">ICEberg</span>
                        </a>
                        <!-- Card 2: UniProt -->
                        <a href="http://www.uniprot.org/" target="_blank" class="link-card">
                            <div class="link-card-img">
                                <img src="images/logo_UniProt.png" alt="UniProt">
                            </div>
                            <span class="link-card-name">UniProt</span>
                        </a>
                        <!-- Card 3: ISCR Elements -->
                        <a href="http://medicine.cf.ac.uk/infect-immun/research/infection/antibacterial-agents/iscr-elements/" target="_blank" class="link-card">
                            <div class="link-card-img">
                                <!-- No logo for this link in the original -->
                            </div>
                            <span class="link-card-name">ISCR Elements</span>
                        </a>
                        <!-- Card 4: Cyanobase -->
                        <a href="http://genome.microbedb.jp/cyanobase" target="_blank" class="link-card">
                            <div class="link-card-img">
                                <img src="images/logo_cyanobase.png" alt="Cyanobase">
                            </div>
                            <span class="link-card-name">Cyanobase</span>
                        </a>
                        <!-- Card 5: Pfam -->
                        <a href="http://pfam.xfam.org/" target="_blank" class="link-card">
                            <div class="link-card-img">
                                <img src="images/logo_Pfam.png" alt="Pfam">
                            </div>
                            <span class="link-card-name">Pfam</span>
                        </a>
                        <!-- Card 6: INTEGRALL -->
                        <a href="http://integrall.bio.ua.pt/" target="_blank" class="link-card">
                            <div class="link-card-img">
                                <img src="images/logo_IntegronDB.png" alt="INTEGRALL">
                            </div>
                            <span class="link-card-name">INTEGRALL — The Integron Database</span>
                        </a>
                        <!-- Card 7: EcoliWiki -->
                        <a href="http://ecoliwiki.net/colipedia/index.php/Welcome_to_EcoliWiki" target="_blank" class="link-card" title="EcoliWiki">
                            <div class="link-card-img">
                                <img src="images/logo_ECO.png" alt="EcoliWiki">
                            </div>
                            <span class="link-card-name">EcoliWiki</span>
                        </a>
                        <!-- Card 8: Tn Number Registry -->
                        <a href="https://transposon.lstmed.ac.uk/" target="_blank" class="link-card">
                            <div class="link-card-img">
                                <img src="images/logo_TransposonRegistry.png" alt="Tn Number Registry">
                            </div>
                            <span class="link-card-name">Tn Number Registry</span>
                        </a>
                        <!-- Card 9: TnPedia -->
                        <a href="https://tncentral.ncc.unesp.br/TnPedia/index.php" target="_blank" class="link-card">
                            <div class="link-card-img">
                                <img src="images/logo_Tn.png" alt="TnPedia">
                            </div>
                            <span class="link-card-name">TnPedia</span>
                        </a>
                        <!-- Card 10: Tn Central -->
                        <a href="https://tncentral.proteininformationresource.org/" target="_blank" class="link-card">
                            <div class="link-card-img">
                                <img src="images/logo_Tn.png" alt="Tn Central">
                            </div>
                            <span class="link-card-name">Tn Central</span>
                        </a>
                    </div>
                </div>
                <button class="links-see-more" aria-label="Show more Databases links" title="Show more">&#10132;</button>
            </div>
        </section>

        <!-- ============================================= -->
        <!-- Category: Institutions (6 links)              -->
        <!-- ============================================= -->
        <section class="links-category">
            <h3 class="links-category-title">Institutions</h3>
            <div class="links-row">
                <div class="links-carousel">
                    <div class="links-track">
                        <!-- Card 1: CNRS -->
                        <a href="http://www.cnrs.fr/index.php" target="_blank" class="link-card" title="CNRS">
                            <div class="link-card-img">
                                <img src="images/logo_CNRS.svg" alt="CNRS">
                            </div>
                            <span class="link-card-name">CNRS</span>
                        </a>
                        <!-- Card 2: Université de Louvain -->
                        <a href="https://uclouvain.be/en/index.html" target="_blank" class="link-card" title="Université de Louvain">
                            <div class="link-card-img">
                                <img src="images/logo_UnivLouvain.png" alt="Université catholique de Louvain">
                            </div>
                            <span class="link-card-name">Univ. de Louvain</span>
                        </a>
                        <!-- Card 3: EMBL -->
                        <a href="http://www.embl.de/" target="_blank" class="link-card">
                            <div class="link-card-img">
                                <img src="images/logo_EMBL.png" alt="EMBL">
                            </div>
                            <span class="link-card-name">EMBL</span>
                        </a>
                        <!-- Card 4: NCBI -->
                        <a href="http://www.ncbi.nlm.nih.gov/" target="_blank" class="link-card">
                            <div class="link-card-img">
                                <img src="images/logo_NCBI.png" alt="NCBI">
                            </div>
                            <span class="link-card-name">NCBI</span>
                        </a>
                        <!-- Card 5: NCBI Taxonomy -->
                        <a href="http://www.ncbi.nlm.nih.gov/taxonomy" target="_blank" class="link-card">
                            <div class="link-card-img">
                                <img src="images/logo_NCBI.png" alt="NCBI Taxonomy">
                            </div>
                            <span class="link-card-name">NCBI Taxonomy</span>
                        </a>
                        <!-- Card 6: Institut Pasteur -->
                        <a href="http://www.pasteur.fr/fr" target="_blank" class="link-card">
                            <div class="link-card-img">
                                <img src="images/logo_Pasteur.png" alt="Institut Pasteur">
                            </div>
                            <span class="link-card-name">Institut Pasteur</span>
                        </a>
                    </div>
                </div>
                <button class="links-see-more" aria-label="Show more Institutions links" title="Show more">&#10132;</button>
            </div>
        </section>

        <!-- ============================================= -->
        <!-- Category: Tools (5 links)                     -->
        <!-- ============================================= -->
        <section class="links-category">
            <h3 class="links-category-title">Tools</h3>
            <div class="links-row">
                <div class="links-carousel">
                    <div class="links-track">
                        <!-- Card 1: Migale -->
                        <a href="http://migale.jouy.inra.fr/?q=accueil" target="_blank" class="link-card">
                            <div class="link-card-img">
                                <img src="images/logo_migale.png" alt="Migale">
                            </div>
                            <span class="link-card-name">Migale — INRA Jouy en Josas</span>
                        </a>
                        <!-- Card 2: Multalign -->
                        <a href="http://multalin.toulouse.inra.fr/multalin/" target="_blank" class="link-card">
                            <div class="link-card-img">
                                <img src="images/logo_Multalign.png" alt="Multalign">
                            </div>
                            <span class="link-card-name">Multalign</span>
                        </a>
                        <!-- Card 3: mfold Web Server -->
                        <a href="http://www.unafold.org/mfold/applications/dna-folding-form.php" target="_blank" class="link-card">
                            <div class="link-card-img">
                                <!-- No logo for this link in the original -->
                            </div>
                            <span class="link-card-name">The mfold Web Server</span>
                        </a>
                        <!-- Card 4: WebLogo -->
                        <a href="http://weblogo.berkeley.edu/logo.cgi" target="_blank" class="link-card">
                            <div class="link-card-img">
                                <img src="images/logo_WebLogo.png" alt="WebLogo">
                            </div>
                            <span class="link-card-name">WebLogo</span>
                        </a>
                        <!-- Card 5: Expasy -->
                        <a href="http://www.expasy.org/tools/" target="_blank" class="link-card">
                            <div class="link-card-img">
                                <img src="images/logo_Expasy.png" alt="Expasy">
                            </div>
                            <span class="link-card-name">Expasy</span>
                        </a>
                    </div>
                </div>
                <button class="links-see-more" aria-label="Show more Tools links" title="Show more">&#10132;</button>
            </div>
        </section>

        <!-- ============================================= -->
        <!-- JS: Horizontal carousel slider                -->
        <!-- WHY: Clicking the arrow slides the card track -->
        <!--      to the right by 4 cards. Wraps back to   -->
        <!--      the start when reaching the end.         -->
        <!-- ============================================= -->
        <script>
        document.querySelectorAll('.links-category').forEach(function(section) {
            var track = section.querySelector('.links-track');
            var btn = section.querySelector('.links-see-more');
            var cards = track.querySelectorAll('.link-card');
            var totalCards = cards.length;
            var visibleCards = 4;
            var currentOffset = 0; /* how many cards we've scrolled past */

            /* Hide arrow if 4 or fewer cards (nothing to scroll) */
            if (totalCards <= visibleCards) {
                btn.classList.add('hidden');
                return;
            }

            btn.addEventListener('click', function() {
                currentOffset += visibleCards;

                /* If we've scrolled past all cards, wrap back to start */
                if (currentOffset >= totalCards) {
                    currentOffset = 0;
                }

                /* Calculate the pixel offset to translate the track.
                   Each card is 25% of the carousel width, with 1em gaps.
                   We use the first card's actual width + gap for precision. */
                var cardStyle = window.getComputedStyle(cards[0]);
                var cardWidth = cards[0].offsetWidth;
                var gap = parseFloat(window.getComputedStyle(track).gap) || 16;
                var translateX = currentOffset * (cardWidth + gap);

                track.style.transform = 'translateX(-' + translateX + 'px)';
            });
        });
        </script>

    </article>

    <?php include_once('include/footer.inc.php'); ?>
</div> <!-- Fin du div page -->
</body>
</html>