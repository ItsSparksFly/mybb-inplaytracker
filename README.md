# Inplaytracker 3.0
Der <strong>Inplaytracker</strong> ist ein für Rollenspielzwecke in PHP und jscript entwickeltes Plugin, das mit der Forensoftware <em>MyBB 1.8</em> kompatibel ist. Diese Software wird unter der <a href="https://www.gnu.de/documents/lgpl-3.0.de.html" target="_blank">GNU LGPL V3-Lizenz</a> veröffentlicht. 

# Funktionsweise
Der <em>Inplaytracker</em> ermöglicht es Mitgliedern des Forums, andere Mitglieder bei Erstellen eines neuen Threads zu "taggen" - diese erhalten über das Plugin <a href="https://github.com/MyBBStuff/MyAlerts" target="_blank">MyAlerts</a> im Anschluss eine Benachrichtigung. Darüber hinaus lassen sich Spieldatum und Spielort, sowie eine kurze Beschreibung des Spielgeschehens für diesen Thread hinterlegen. Eine Übersicht aller getaggten Szenen liefert das Plugin ebenso mit wie einen nummerische Angabe aller offenen Szenen im Headerbereich des Forums.

# Empfohlene Plugins
<a href="https://github.com/MyBBStuff/MyAlerts" target="_blank">MyAlerts</a> von euanT<br />
<a href="http://doylecc.altervista.org/bb/downloads.php?dlid=4&cat=1" target="_blank">Enhanced Account Switcher</a> von doylecc<br />
<a href="https://github.com/aheartforspinach/Archivierung" target="_blank">Themenarchivierung</a> von aheartforspinach<br />
<a href="https://github.com/aheartforspinach/Posting-Erinnerung" target="_blank">Posting-Erinnerung</a> von aheartforspinach<br />


# Upgrade
Falls die Version <strong>2.0</strong> (latest release) installiert ist, wird folgendes Upgrade-Vorgehen unbedingt empfohlen:

- ein BackUp der Datenbank machen
- "Inplaytracker 2.0" im AdminCP deaktivieren (NICHT deinstallieren!) => alte Templates werden gelöscht
- Inplaytracker 3.0 installieren & aktivieren
- misc.php?action=do_upgrade aufrufen => die aktuellen Inplay-Daten werden an die neuen Datenbankstrukturen angepasst, alte Einstellungen und Datenbankänderungen werden gelöscht