<nav>
	<ul>
		<li<?php if ($nav_en_cours == 'home') {echo ' id="en-cours"';} ?>><a href="https://www-is.biotoul.fr/index.php">HOME</a></li>
  		<li<?php if ($nav_en_cours == 'infos') {echo ' id="en-cours"';} ?>><a href="#">INFORMATION</a>
        	<ul class="sousmenu">
				<li><a href="/general_information.php">General information</a></li>
                <li><a href="/index.php">IS families</a>
                    <ul class="sousmenu2">
					<li><a href="/Documents/family_characteristics.php">Major features of procaryotes IS families</a></li>
                	<li><a href="/under_construct.php">DDE motifs</a></li>
                	<li><a href="/under_construct.php">Family information</a></li>
					</ul>        
                </li>
				<li><a href="/index.php">Nomenclature</a>
                    <ul class="sousmenu2">
					<li><a href="/nomenclature.php">Nomenclature</a></li>
                	<li><a href="/list_names_attributed.php">List names currently attributed</a></li>
                	<li><a href="/reserved_blocks.php">Reserved blocks of IS previously attributed</a></li>
					</ul>
                </li>        
                <li><a href="/links.php">Links</a></li>
			</ul>
        </li>
		<li<?php if ($nav_en_cours == 'tools') {echo ' id="en-cours"';} ?>><a href="#">TOOLS</a>
        	<ul class="sousmenu">
				<li><a href="/search.php">Search</a></li>
                <li><a href="/blast.php">Blast</a></li>
			</ul>        
        </li>
		<li<?php if ($nav_en_cours == 'submission') {echo ' id="en-cours"';} ?>><a href="#">SUBMISSION</a>
        	<ul class="sousmenu">
				<li><a href="/request_name_form.php">Request a name</a></li>
                <li><a href="/submission.php">Submit an IS</a></li>
			</ul>        
        </li>
		<li<?php if ($nav_en_cours == 'genomes') {echo ' id="en-cours"';} ?>><a href="#">GENOMES</a>
        	<ul class="sousmenu">
				<li><a href="http://www-genome.biotoul.fr/ISbrowser.php" target="_blank">ISbrowser</a></li>
                <li><a href="http://issaga.biotoul.fr" target="_blank">ISsaga</a></li>
			</ul>        
        </li>
		<li<?php if ($nav_en_cours == 'about') {echo ' id="en-cours"';} ?>><a href="#">ABOUT</a>
        	<ul class="sousmenu">
				<li><a href="/about.php">About</a></li>
                <li><a href="/howto.php">How to ...</a>
                    <ul class="sousmenu2">
					<li><a href="/howto.php">cite ?</a></li>
                	<li><a href="/howto.php">Request an attribution number ?</a></li>
                	<li><a href="/howto.php">Submit a sequence ?</a></li>
					</ul>        
				</li>
				<li><a href="/credits.php">Credits</a></li>
                <li><a href="/feedback.php">Feedback</a></li>
                <li><a href="/under_construct.php">ISfinder team</a></li>
			</ul>        
        </li>
	</ul>
</nav>
