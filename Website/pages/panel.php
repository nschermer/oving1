<div class="panel panel-left panel-cover theme-dark">
	<div class="page">
		<div class="page-content">
			<div class="block">
				<div class="row">
					<a class="link icon-only col panel-close" href="#" id="darkbtn" title="Donkere Modus">
						<i class="f7-icons icon">circle_half</i>
					</a>
					<a class="link icon-only col panel-close" href="/settings" title="Instellingen">
						<i class="f7-icons icon">settings</i>
					</a>
				</div>
			</div>
			<div class="list no-hairlines-between">
				<ul>
<?php

function template_panel_item ($href, $icon, $title) {
	print ('<li>');
		print ('<a href="'.$href.'" class="item-link item-content panel-close">');
			print ('<div class="item-media">');
				print ('<i class="f7-icons icon">'.$icon.'</i>');
			print ('</div>');
			print ('<div class="item-inner">');
				print ('<div class="item-title">'.$title.'</div>');
			print ('</div>');
		print ('</a>');
	print ('</li>');
}

function template_panel_divider ($title) {
	print ('<li class="item-divider">'.$title.'</li>');
}

/* User menu */
template_panel_item ('/', 'data', 'Dashboard');
template_panel_item ('/power', 'bolt', 'Aandrijving');
template_panel_item ('/log', 'alarm', 'Logboek');
template_panel_item ('/trends', 'graph_square', 'Trends');
template_panel_item ('/dp', 'compass', 'Dynamic Position');

?>
				</ul>
			</div>
		</div>
	</div>
</div>