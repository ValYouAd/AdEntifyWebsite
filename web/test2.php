<?php

$emails = 'jgarcin@nouvelobs.com
geo_the_surfer@hotmail.com
Valerie GAUTHIER
antoine@intersport-bourg.com
Marie Gaymard
laffontg@club-internet.fr
louisge63@gmail.com
gosset-grainville@bdgs-associes.com
helene.gourlet@gmail.com
Mme Elisabeth Grand
leonor.grandsire@nncuni.com
gilles.grapinet@atos.net
francoise.gri@fr.groupepvcp.com
pauline.grimaldi@nbcnuni.com
LAPAQUE Sebastien
GRIMALDI Stéphane
jerome.grivet@ca.cib.com
maurice.guillou@gmail.com
j.m.guilloux@ieee.org
claudie.haignere@universcience.fr
halperinsophie@yahoo.fr
ronnie.hawkins@ge.com
jean-paul.herteman@safran.fr
gwendalhervouet@yahoo.fr
hugues.de-la-marnierre@sgcib.com
xavier.huillard@vinci.com
Typhaine Jaffrézic
jamet.marcantoine@gmail.com
Adrien Jaulmes
olivierjay@free.fr
jean.leviol@diplomatie.gouv.fr
jeanclaude.najar@gmail.com
jmpainvin@deutsch.net
karine.montagut@allenovery.com
kacim.kellal@yahoo.fr
khocine@editions-lattes.fr
bruno.lafont@lafarge.com
ghislainlafont@wanadoo.fr
bestelling@rudolf-mathis.dk
bruno le bourjois
Erwan le Méné
theophane.lemene@gmail.com
jalepape@airfranceklm.com
molefevre@beaussant-lefevre.com
francoislegrand@yahoo.fr
vlejeune@lefigaro.fr
lelandais@hotmail.fr
mathilde.lemoine@hsbc.fr
philippe.lentschener@mccann.com
Leoty, Cedric
denis.lepee@edf.fr
heloiselesur@blackberry.orange.fr
jean-pierre.jouyet@caissedesdepots.fr
jean-pierre.letartre@fr.ey.com
lethuill@club-internet.fr
alain@weborama.com
hubert.loiseleur-des-longchamps@total.com
Anne-Sophie Lustin
maffesoli@ceaq-sorbonne.org
CMAKARIAN@lexpress.fr
arielle.malard@fr.rotschild.com
pmaniere@footprintconsultants.fr
louismarie.meyer@yahoo.fr
marin_lamellet@hotmail.com
matthieu.lejeune@rolgroup.com
maxime.leclere@direccte.gouv.fr
jean.meimon@devinci.fr
jmm@messiermaris.com
gerard.mestralet@gdfsuez.com
bene y
sfmichel@wanadoo.fr
miguel.de-fontenay@mazars.fr
mireille.faugere@sap.aphp.fr
blaise.mistler@sacem.fr
vmorali@fimalac.com
vmorgon@eurazeo.com
thierry.mor1@gmail.com
jean-yves.naouri@publicisgroupe.com
jcnarcy@yahoo.fr
Salvucci, Ombretta (NIH/NCI) [C]
carlota.p1v1@verizon.net
Catherine (gmail.com)
patrickdziewolski@bredinprat.com
Jean-Pierre Philippe
p.lemoine@lasergroup.eu
matthieu.pigasse@lazard.fr
Thibaut Piotrowski
sgpoitrinal@noos.fr
elisabeth.pommereau@orange.fr
freiniche@coca-cola.com
Bruno revellin-falcoz
richard boutry
rr@ricol-lasteyrie.fr
jean-claude.rivalland@allenovery.com
vdrivaz@aol.com
bruno.roger@lazard.fr
laurentroux73@wanadoo.fr
jerome.ruskin@usbek-et-rica.fr
arthur.sadoun@conseil.publicis.fr
jamelsaiki@yahoo.fr
psayer@eurazeo.com
dianesegalen@gmail.com
sylvie.gleises@axa.com
Guy Tardieu
olgatheo26@yahoo.fr
philippe.varin@mpsa.com
pierre.vellay@gmail.com
serge.weinberg@weinbergcapital.com
thierry.willieme@ge.com
M. Benoit Yvert
Mme Sylvie Yvert
rzarader@equancy.com';

preg_match_all('<[\w.-]+@[\w.-]+\.[a-zA-Z]{2,6}>', $emails, $out);

$emails = array();
$query = 'INSERT INTO `autovalidation_emails`(`id`, `email`) VALUES ';
$i = 0;
for($i = 0; $i < count($out[0]); $i++) {
    $email = $out[0][$i];
    if (!in_array($email, $emails)) {
        $emails[] = $email;
        $query .= '("","'.$email.'"), ';
    }
}

echo $query;

?>