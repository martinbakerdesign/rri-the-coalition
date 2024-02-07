<?php /* Template Name: The Coalition Page Template */

require_once RRI_MAPPING_TOOL_PLUGIN_PATH . 'includes/taxonomies/rri-mapping-tool-taxonomies.php';
require_once RRI_MAPPING_TOOL_PLUGIN_PATH . 'includes/roles/rri-mapping-tool-roles.php';
require_once RRI_MAPPING_TOOL_PLUGIN_PATH . 'includes/class-rri-mapping-tool-utils.php';

function get_filter_option_markup($taxonomy, $term_id, $label)
{
      $id = $taxonomy . '-' . $term_id;
      $name = $taxonomy . '-' . $term_id;
      $value = $term_id;
      return '<label for="' . $taxonomy . '-' . $term_id . '">
        <input type="checkbox" name="' . $name . '" id="' . $id . '" value="' . $value . '" />
        <span>' . $label . '</span>
    </label>';
}
function get_slideshow_markup ($imgs = [], $classname = '')
{
      if (count($imgs) === 1) {
            return '<img src="'.array_keys($imgs)[0].'" alt="'.array_values($imgs)[0].'" class="media-cover" />';
      }

      $img_markup = '';
      $index = 0;
      foreach ($imgs as $src => $alt) {
            $hidden = $index !== 0
                  ? ''
                  : 'hidden aria-hidden="true"';
            $img_markup .= '<img src="'.$src.'" alt="'.$alt.'" '.$hidden.' />';
            $index++;
      }

      return '<div class="slideshow '.$classname.'" data-current="0">
            '.$img_markup.'
      </div>';
}
function get_filter_toggle_markup($taxonomy_slug, $taxonomy_label, $terms_count, $all_label)
{
      $chevron_icon_markup = get_icon_markup('chevron-down', 14, 'filter__toggle__icon');
      return "<button
        class=\"filter__toggle collapsible__toggle\"
        type=\"button\"
        aria-controls=\"$taxonomy_slug-inputs\"
        id=\"regions-toggle\"
        aria-expanded=\"false\"
    >
    <span
        class=\"filter__toggle__count\"
        aria-hidden=\"true\"
        >$terms_count</span
    >
    <span class=\"filter__toggle__label\">$taxonomy_label</span>
    $chevron_icon_markup
    <span class=\"filter__toggle__preview\">$all_label</span>
    </button>";
}
function get_button_markup ($props = array())
{
      if (!$props || !count($props)) return '';
      $label = $props['label'] ?? '';
      $href = $props['href'] ?? '';
      $target = $props['target'] ?? '_self';
      $id = $props['id'] ?? '';
      $classname = $props['classname'] ?? '';
      $size = isset($props['size']) && strlen($props['size']) > 0
            ? 'button-'.$props['size']
            : '';
      $variant = isset($props['variant']) && strlen($props['variant']) > 0
            ? 'button-'.$props['variant']
            : '';
      $prefix_markup = isset($props['prefix']) && strlen($props['prefix']) > 0
      ? '<span class="prefix-slot">'.$props['prefix'].'</span>'
      : '';
      $suffix_markup = isset($props['suffix']) && strlen($props['suffix']) > 0
      ? '<span class="suffix-slot">'.$props['suffix'].'</span>'
      : '';
      $label_markup = isset($props['label']) && strlen($props['label'])
            // ? '<span>'.$label.'</span>'
            ? $label : '';
      $classname = trim($size.' '.$variant.' '.$classname);
      $attrs = array();
      if (isset($props['attrs']))
      {
            foreach ($props['attrs'] as $attr => $value)
            {
                  $attrs[] = $attr.'="'.$value.'"';
            }
      }
      $attrs = join(' ', $attrs);

      return '<a '.($id ? 'id="'.$id.'"' : '').' '.(strlen($classname) ? 'class="'.$classname.'"' : '').' href="'.$href.'" target="'.$target.'" '.$attrs.'>'.$prefix_markup.$label_markup.$suffix_markup.'</a>';
}
function get_icon_markup($icon, $size = 14, $classname = NULL, $label = '') 
{
      $title = $label && strlen($label)
            ? '<title>'.$label.'</title>'
            : '';

      return "<svg class=\"icon $classname\" width=\"$size\" height=\"$size\" aria-hidden=\"true\">
            $title
            <use href=\"#symbol-$icon-$size\" />
      </svg>";
}
function get_tab_markup($id, $classname, $selected, $controls, $content)
{
      $tabindex = !$selected ? 'tabindex="-1"' : '';
      return '<button id="' . $id . '" class="' . $classname . '" type="button" role="tab" aria-selected="' . json_encode($selected) . '" ' . $tabindex . ' aria-controls="' . $controls . '">' . $content . '</button>';
}
function get_category_tag_markup($term)
{
      $term_id = $term['id'] ?? $term['term_id'] ?? $term['name'];
      $taxonomy = $term['taxonomy'] ?? NULL;
      return '<li class="category-tag" data-id="' . $term_id . '" data-category="' . $taxonomy . '">' . $term['name'] . '</li>';
}
function get_tabpanel_markup($id, $classname, $selected, $labelled_by, $content)
{
      $hidden = $selected ? '' : 'hidden';
      $tabindex = $selected ? 'tabindex="0"' : '';

      return '<div id="' . $id . '" class="' . $classname . '" ' . $hidden . ' aria-labelledby="' . $labelled_by . '" ' . $tabindex . '>' . $content . '</div>';
}
function get_about_panel_markup ($member = NULL)
{
      $about = $member && isset($member['about'])? $member['about'] : '';
      $vision = $member && isset($member['vision'])? $member['vision'] : '';
      $mission = $member && isset($member['mission'])? $member['mission'] : '';

      return '<section class="member-focus__about">
      <h4 class="member-focus__about__heading">'.__("About", "rri-mapping-tool").'</h4>
      <div class="member-focus__about__content">'.$about.'</div>
      </section>
      <section class="member-focus__vision">
      <h4 class="member-focus__vision__heading">'.__("Vision", "rri-mapping-tool").'</h4>
      <div class="member-focus__vision__content">'.$vision.'</div>
      </section>
      <section class="member-focus__mission">
      <h4 class="member-focus__mission__heading">'.__("Mission", "rri-mapping-tool").'</h4>
      <div class="member-focus__mission__content">'.$mission.'</div>
      </section>';
}
function get_achievements_panel_markup ($member = NULL)
{
      $achievements = ($member && isset($member['achievements']))
            ? $member['achievements']
            : array();

      $grouped_by_year = group_achievements_by_year($achievements);

      $markup = count($achievements) > 0
      ? join('',array_map(function ($year) use($grouped_by_year) {
            return get_achievement_group_markup($year, $grouped_by_year[$year]);
      },array_keys($grouped_by_year)))
      : '';

      return '<ul class="achievements">
      '.$markup.'
      </ul>';
}
function group_achievements_by_year ($achievements)
{
      $grouped = array();
      foreach($achievements as $achievement)
      {
            if (!isset($achievement['date'])) continue;

            $year = date('Y',strtotime($achievement['date']));

            if (!isset($grouped[$year])) {
                  $grouped[$year] = array();
            }
            $grouped[$year][] = $achievement;
      }
      return $grouped;
}
function get_achievement_group_markup ($year, $items)
{
      return '<li class="achievement-group">
            <h4 class="achievement-group__year">'.$year.'</h4>
            <ul class="achievement-group__items">
                  '.join('\n',array_map('get_achievement_markup',$items)).'
            </ul>
      </li>';
}
function get_achievement_markup ($achievement)
{
      $has_url = isset($achievement['url']) && strlen($achievement['url']) > 0;
      $heading = '<h5>'.$achievement['description'].'</h5>';
      $contents = $has_url
            ? get_button_markup(array(
                  'href' => $achievement['url'],
                  'target' => "_blank",
                  "label" => $heading
            ))
            : $heading;

      return '<li class="achievement">'.$contents.'</li>';
}
function get_anchor_markup ($href, $label = '', $classname = '')
{
      return get_button_markup(array(
            'href' => $href,
            'label' => $label,
            'classname' => 'anchor '.$classname
      ));
}
function get_project_card_markup($context_slug)
{
      return function ($project) use ($context_slug) {
            $name = $project['name'];
            $thumbnail = $project['thumbnail'];
            $anchor = get_anchor_markup('?focus=project&id=' . $project['id'] . '#' . $context_slug, '');

            return '<li data-id="' . $project['id'] . '" class="project-card">
                  '.$anchor.'
                  <div class="project-card__thumbnail">
                  <img src="' . $thumbnail . '" alt="' . $name . '" />
                  </div>
                  <h4 class="project-card__name">' . $name . '</h4>
            </li>';
      };
}
function get_member_projects_markup($context_slug, $projects)
{
      $project_cards = join('', array_map(get_project_card_markup($context_slug), $projects));

      return '<ul class="member__project-cards">' . $project_cards . '</ul>';
}
function get_member_focus_section($slug, $member = NULL, $items = array()) 
{
      $member_id = $member['id'] ?? NULL;
      $id = !is_null($member)
            ? $slug . '--' . $member_id
            : $slug . '__member';
      $class_prefix = "member-focus";
      $name = $member['name'] ?? NULL;
      $logo = $member['logo'] ?? NULL;
      $abbreviation = $member['abbreviation'] ?? NULL;
      $member_type = $member['member_type']['name'] ?? NULL;
      $established = $member['established'] ?? NULL;
      $url = $member['url'] ?? NULL;
      $topics = $member['topics'] ?? array();
      $expertise = $member['expertise'] ?? array();
      $merged_terms = array_merge($topics, $expertise);
      $categories = !is_null($member) ? join('', array_map('get_category_tag_markup', $merged_terms)) : NULL;
      $projects = get_member_projects_markup($slug, !is_null($member) ? $member['projects'] : array());
      $bio = $member['bio'] ?? NULL;
      $vision = $member['vision'] ?? NULL;
      $mission = $member['mission'] ?? NULL;

      $projects_count = $member && isset($member['projects'])? count($member['projects']) : 0;
      $achievements_panel = get_achievements_panel_markup($member);

      $select = get_select(
            NULL,
            'tab__content-select',
            '<h3 class="' . $class_prefix . '__name">' . $name . '</h3>',
            $items
      );
      $website_link = get_button_markup(array(
            'target' => "_blank",
            'href' => $url,
            'classname' => $class_prefix . '__website-link',
            'size' => 'sm',
            'variant' => 'ghost',
            'label' => 'Visit website',
            'suffix' => get_icon_markup("external-site",14)
      ));

      return '<section id="' . $id . '" class="' . $class_prefix . '" data-id="' . $member_id . '" hidden aria-hidden="true">
        <header class="' . $class_prefix . '__header">
            <div class="' . $class_prefix . '__logo"><img src="' . $logo . '" alt="' . $name . '" /></div>
            <div class="' . $class_prefix . '__abbreviation">' . $abbreviation . '</div>
            ' . $select . '
            <div class="' . $class_prefix . '__meta">
                <div class="' . $class_prefix . '__type">' . $member_type . '</div>
                <div class="' . $class_prefix . '__established">' . __('Established', 'rri-mapping-tool') . ' ' . $established . '</div>
                '.$website_link.'
            </div>
        </header>
        <ul class="' . $class_prefix . '__categories">' . $categories . '</ul>
        <section class="' . $class_prefix . '__content">
            <div class="' . $class_prefix . '__tabs" role=\"tablist\">
                ' . get_tab_markup($slug . '__member-focus__tab--projects', 'member-focus__tab', true, $slug . '__member-focus__projects', __('Project Highlights', "rri-mapping-tool").' <span class="count">'.$projects_count.'</span>') . '
                ' . get_tab_markup($slug . '__member-focus__tab--about', 'member-focus__tab', false, $slug . '__member-focus__about', __('About', "rri-mapping-tool")) . '
                ' . get_tab_markup($slug . '__member-focus__tab--achievements', 'member-focus__tab', false, $slug . '__member-focus__achievements', __('Achievements', "rri-mapping-tool")) . '
            </div>
            ' . get_tabpanel_markup($slug . '__member-focus__projects', '', true, $slug . '__member-focus__tab--projects', $projects) . '
            ' . get_tabpanel_markup($slug . '__member-focus__about', '', false, $slug . '__member-focus__tab--about', get_about_panel_markup(($member))) . '
            ' . get_tabpanel_markup($slug . '__member-focus__achievements', '', false, $slug . '__member-focus__tab--achievements', $achievements_panel) . '
        </section>
    </section>';
}
function get_project_focus_section($slug, $items = array())
{
      $id = $slug . '__project';
      $class_prefix = "project-focus";

      $select = get_select(
            NULL,
            'tab__content-select',
            '<h3 class="' . $class_prefix . '__name"></h3>',
            $items
      );
      $member_link = get_button_markup(array(
            'classname' => $class_prefix . '__member',
            'label' => '<img src="" alt="" class="' . $class_prefix . '__member__logo" />
            <span class="' . $class_prefix . '__member__name"></span>'
      ));
      $website_link = get_button_markup(array(
            'target' => '_blank',
            'classname' => $class_prefix . '__website-link',
            'size' => 'sm',
            'variant' => 'ghost',
            'label' => 'Visit website',
            'suffix' => get_icon_markup("external-site",14)
      ));

      return '<section id="' . $id . '" class="' . $class_prefix . '" data-id="" hidden aria-hidden="true">
        <header class="' . $class_prefix . '__header">
            <div class="' . $class_prefix . '__thumbnail"><img src="" alt="" /></div>
            <div class="'.$class_prefix.'__type">Project</div>
            ' . $select . '
            <div class="'.$class_prefix.'__meta">
            '.$member_link.$website_link.'
            </div>
        </header>
        <ul class="' . $class_prefix . '__categories"></ul>
        <div class="' . $class_prefix . '__description"></div>
    </section>';
}
function get_fellow_focus_section($slug, $items = array())
{
      $id = $slug . "__fellow";
      $class_prefix = "fellow-focus";

      $select = get_select(
            NULL,
            'tab__content-select',
            '<h3 class="' . $class_prefix . '__name"></h3>',
            $items
      );

      return '<section id="' . $id . '" class="' . $class_prefix . '" data-id="" hidden aria-hidden="true">
        <div class="' . $class_prefix . '__thumbnail">
            <img src="" alt="" class="media-cover" />
        </div>
        <header class="' . $class_prefix . '__header">
            <div class="' . $class_prefix . '__type">Fellow</div>
            ' . $select . '
        </header>
        <ul class="' . $class_prefix . '__categories"></ul>
        <div class="' . $class_prefix . '__bio"></div>
    </section>';
}
function get_search_filter_group($heading, $taxonomy_slug, $terms)
{
      $inputs = array();

      foreach ($terms as $term) {
            array_push($inputs, get_filter_option_markup($taxonomy_slug, $term['term_id'], $term['name']));
      }

      $inputs = join('', $inputs);
      $all_label = __("All", "rri-mapping-tool");
      $toggle = get_filter_toggle_markup($taxonomy_slug, $heading, count($terms), $all_label);

      return "<fieldset
        class=\"filter collapsible\"
        data-state=\"closed\"
        data-taxonomy=\"$taxonomy_slug\"
    >
        $toggle

        <div
        id=\"$taxonomy_slug-inputs\"
        hidden
        aria-labelledby=\"$taxonomy_slug-toggle\"
        class=\"filter__options collapsible__collapsible\"
        >

            <label for=\"$taxonomy_slug-all\">
                <input
                    type=\"checkbox\"
                    name=\"$taxonomy_slug-all\"
                    id=\"$taxonomy_slug-all\"
                    value=\"$taxonomy_slug-all\"
                    checked=\"\"
                />
                <span>$all_label</span>
            </label>

            $inputs

        </div>

    </fieldset>";
}
function get__select__toggle($id, $label)
{
      $id = $id && strlen($id)
            ? $id . '__toggle'
            : NULL;
      return '<button type="button" id="' . $id . '" role="combobox" aria-controls="" aria-expanded="false" aria-autocomplete="none" dir="ltr" data-state="closed" data-toggle>
        <span data-label>' . $label . '</span>
        ' . get_icon_markup('chevron-up-down',14) . '
    </button>';
}
function get_select_option($value, $label, $item_id, $type, $selected)
{
      $types = [
            'member' => __('Partner', 'rri-mapping-tool'),
            'partner' => __('Partner', 'rri-mapping-tool'),
            'collaborator' => __('Collaborator', 'rri-mapping-tool'),
            'project' => __('Project', 'rri-mapping-tool'),
            'fellow' => __('Fellow', 'rri-mapping-tool'),
      ];

      return '<div role="option" aria-selected="' . json_encode($selected) . '" tabindex="-1" data-value="' . $value . '" data-item-id="'.$item_id.'" data-option><span class="select-option__label">' . $label . '</span> <span class="select-option__type">'.($types[$type] ?? '').'</span></div>';
}
function get_select($id, $classname, $label, $items = array())
{
      $listbox_id = $id && strlen($id)
            ? $id . '__listbox'
            : NULL;
      $options = array();

      $i = 0;
      usort($items, function ($a, $b) {
            return $a['name'] < $b['name'] ? -1 : 1;
      });

      foreach ($items as $item) {
            $item = (array) $item;
            $id = $item['id'] ?? $item['term_id'] ?? NULL;
            $type = isset($item['type']) && strlen($item['type'])
                  ? $item['type']
                  : NULL;
            $taxonomy = isset($item['taxonomy']) && strlen($item['taxonomy'])
                  ? 'category'
                  : NULL;
            $item_id = (($type || $taxonomy) && $id)
                  ? ($type ?? $taxonomy).'--'.$id
                  : '';
            $type = isset($item['type'])
                  ? ($item['type'] != 'member'
                        ? $item['type']
                        : $item['member_type']['slug'])
                  : null;
            $options[] = get_select_option(
                  $id,
                  $item['name'],
                  $item_id,
                  $type,
                  $i == 0
            );
            $i++;
      }
      $options = join('', $options);

      return '<div id="' . $id . '" class="select ' . $classname . '"  data-state="closed">
        ' . get__select__toggle($id, $label) . '
        <div id="' . $listbox_id . '" role="listbox" tabindex="-1" data-listbox>
            <div role="presentation">
            ' . $options . '
            </div>
        </div>
    </div>';
}
function get_taxonomy_card($taxonomy_slug, $term_id, $name, $thumbnail)
{
      $href = '?focus=category&id=' . $term_id . '#' . $taxonomy_slug;
      $anchor = get_anchor_markup($href,$name);

      return "<li class=\"category__card\">
        $anchor
        <div class=\"category__card__thumb\">
            <img src=\"$thumbnail\" alt=\"$name\" class=\"media-cover\"/>
        </div>
        <h3 class=\"category__card__heading\">$name</h3>
    </li>";
}
function get_taxonomy_showcase($taxonomy_slug, $term_id, $terms, $name, $description, $thumbnail)
{
      $id = $taxonomy_slug . '--' . $term_id;
      $select = get_select(
            $taxonomy_slug . '--' . $term_id . '__select',
            'tab__content-select',
            '<h3 class="category-focus__name">' . $name . '</h3>',
            $terms
      );
      return "<section id=\"$id\" class=\"category-focus\" tabindex=\"-1\" hidden aria-hidden=\"true\" data-id=\"$term_id\">
        $select
        <div class=\"category-focus__thumbnail\">
            <img src=\"$thumbnail\" alt=\"$name\" class=\"media-cover\" />
        </div>
        <div class=\"category-focus__description\">$description</div>
    </section>";
}
function get_taxonomy_section(string $taxonomy_slug, int $tab_number, $description, $terms = array(), $map_items = array())
{
      $back_button = get_back_button_markup($taxonomy_slug);

      $sections = array();
      $cards = array();

      foreach ($terms as $term) {
            $sections[] = get_taxonomy_showcase($taxonomy_slug, $term['term_id'], $terms, $term['name'], $term['description'], $term['thumbnail']);
            $cards[] = get_taxonomy_card($taxonomy_slug, $term['term_id'], $term['name'], $term['thumbnail']);
      }

      $member_focus = get_member_focus_section($taxonomy_slug, NULL, $map_items);
      $project_focus = get_project_focus_section($taxonomy_slug,  $map_items);
      $fellow_focus = get_fellow_focus_section($taxonomy_slug,  $map_items);

      $sections = join('', array_merge($sections, array($member_focus,$project_focus,$fellow_focus)));
      $cards = join('', $cards);


      return '<section role="tabpanel" id="tab--' . $taxonomy_slug . '" aria-labelledby="tab' . $tab_number . '" class="pointer-events-auto" hidden aria-hidden="true">
            '.$back_button.'
            <div class="tab__slide">
                
                <section id="' . $taxonomy_slug . '-landing" class="tab__landing">

                    <div id="' . $taxonomy_slug . '-description" class="mb-26 text-body text-text md:max-w-text">' . $description . '</div>
                    
                    <ul class="gap-4 flex flex-col md:grid md:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">' . $cards . '</ul>

                </section>

                <section class="tab__content">' . $sections . '</section>

                <section id="' . $taxonomy_slug . '__result" class="map-result" hidden aria-hidden="true">
                    
                </section>

            </div>
        </section>';
}
function get_fellow_card($slug, $id, $name, $thumbnail)
{
      $href = '?focus=fellow&id=' . $id . '#' . $slug;
      $anchor = get_anchor_markup($href, $name);

      return "<li class=\"category__card\">
        $anchor
        <div class=\"category__card__thumb\">
            <img src=\"$thumbnail\" alt=\"$name\" class=\"media-cover\" />
        </div>
        <h3 class=\"category__card__heading\">$name</h3>
    </li>";
}
function get_fellow_showcase($slug, $fellow_id, $name, $bio, $thumbnail, $topics, $expertise, $fellows)
{
      $id = $slug . '--' . $fellow_id;
      $categories = array_map('get_category_tag_markup', array_merge($topics, $expertise));
      $categories = join('', $categories);

      $class_prefix = 'fellow-focus';

      $select = get_select(
            'fellow--' . $fellow_id . '__select',
            'tab__content-select',
            '<h3 class="' . $class_prefix . '__name">' . $name . '</h3>',
            $fellows
      );

      return '<section id="' . $id . '" class="category-focus ' . $class_prefix . '" data-id="' . $fellow_id . '" hidden aria-hidden="true">
        <div class="' . $class_prefix . '__thumbnail">
            <img src="' . $thumbnail . '" alt="' . $name . '" class="media-cover" />
        </div>
        <header class="' . $class_prefix . '__header">
            <div class="' . $class_prefix . '__type">Fellow</div>
            ' . $select . '
        </header>
        <ul class="' . $class_prefix . '__categories">
            ' . $categories . '
        </ul>
        <div class="' . $class_prefix . '__bio">' . $bio . '</div>
    </section>';
}
function get_back_button_markup ($slug)
{
      return get_button_markup(array(
            'href' => '#' . $slug,
            'attrs' => array(
                  'hidden' => '',
                  'tabindex' => '-1',
                  'aria-hidden' => 'true'
            ),
            'classname' => 'tab__back',
            'size' => 'md',
            'variant' => 'ghost',
            'prefix' => get_icon_markup('chevron-left', 20),
            'label' => '<span>'.__("Back", "rri-mapping-tool").'</span>'
      ));
}
function get_fellows_section(int $tab_number, $description, $fellows)
{
      $slug = 'fellows';

      $back_button = get_back_button_markup($slug);

      $cards = array();
      $sections = array();

      foreach ($fellows as $fellow) {
            $cards[] = get_fellow_card($slug, $fellow['id'], $fellow['name'], $fellow['thumbnail']);
            $sections[] = get_fellow_showcase(
                  $slug,
                  $fellow['id'],
                  $fellow['name'],
                  $fellow['bio'],
                  $fellow['thumbnail'],
                  $fellow['topics'] ?? [],
                  $fellow['expertise'] ?? [],
                  $fellows
            );
      }

      $sections = join('', $sections);
      $cards = join('', $cards);

      return '<section role="tabpanel" id="tab--' . $slug . '" aria-labelledby="' . get_tab_id($tab_number) . '" class="pointer-events-auto" hidden aria-hidden="true">
            '.$back_button.'
            <div class="tab__slide">
                <section id="fellows-landing" class="cats-landing tab__landing">

                    <div id="fellows-description" class="max-w-text mb-26 text-body text-text">' . $description . '</div>
                    
                    <ul class="gap-4 flex flex-col md:grid md:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">' . $cards . '</ul>

                </section>
                <section class="tab__content" hidden aria-hidden="true">' . $sections . '</section>
            </div>
        </section>';
}
function get_tab_id ($index)
{
      return 'tab--'.$index;
}
function get_directory_result_member($member)
{
      $href = '#directory__member?id=' . $member['id'];
      $name = $member['name'];
      $type = $member['member_type']['name'];
      $link = get_directory_result_link($href, $name, $type);

      return '<li>'.$link.'</li>';
}
function get_directory_result_project($project)
{
      $href = '#directory__project?id=' . $project['id'];
      $name = $project['name'];
      $link = get_directory_result_link($href, $name, 'Project');

      return '<li>'.$link.'</li>';
}
function get_directory_result_fellow($fellow)
{
      $href = '#directory__fellow?id=' . $fellow['id'];
      $name = $fellow['name'];
      $link = get_directory_result_link($href, $name, 'Fellow');

      return '<li>'.$link.'</li>';
}
function get_directory_result_link ($href, $name, $type)
{
      return get_button_markup(array(
            'href' => $href,
            'label' => '<span>' . $name . '</span> <span>' . $type . '</span>'
      ));
}
function get_grouped_directory_results($sorted_results)
{
      $grouped = array();

      foreach ($sorted_results as $item) {
            $letter = strtoupper(substr($item['name'], 0, 1));
            if (!isset($grouped[$letter])) {
                  $grouped[$letter] = array();
            }

            $grouped[$letter][] = $item;
      }

      return $grouped;
}
function get_latest_blog_articles($count = 4)
{
      $q = new WP_Query(array(
            'post_type'         =>  'blog',
            'order'             =>  'DESC',
            'orderby'           =>  'date',
            'post_status'       =>  'publish',
            'posts_per_page'    =>  $count,
            'page'             =>  1
      ));

      return $q->posts;
}
function get_hero_link ($hash, $label)
{
      return get_button_markup(array(
            'href' => '#'.$hash,
            'classname' => 'text-green-700 underline',
            'label' => $label
      ));
}

$data = Rri_Mapping_Tool_Utils::get_coalition_map_data();
$topics = $data['topics'];
$expertise = $data['expertise'];
$regions = $data['regions'];
$countries = $data['countries'];
$members = $data['members'];
$projects = $data['projects'];
$fellows = $data['fellows'];
$partners = $data['partners'];
$collaborators = $data['collaborators'];
$map_data = $data['geojson'];
$json_data = json_encode(array(
      'topics' => $topics,
      'expertise' => $expertise,
      'regions' => $regions,
      'members' => $members,
      'projects' => $projects,
      'fellows' => $fellows,
      'memberTypes' => $data['member_types']
));

$map_items = array_merge(
      $partners,
      $projects,
      $fellows
);
usort(
      $map_items,
      function ($a, $b) {
            return $a['name'] < $b['name'] ? -1 : 1;
      }
);

$directory_results = get_grouped_directory_results($map_items);
?>

<?php get_header(); ?>

<div class="clear"></div>

<link rel="stylesheet" href="/wp-content/plugins/rri-mapping-tool/public/templates/the-coalition/dist/assets/index-hoiyDN47.css" type="text/css" />

<script id="json" class="hidden" hidden aria-hidden="true">
      const jsonData = <?php echo $json_data ?>;
</script>

<svg xmlns="http://www.w3.org/2000/svg" style="width: 1px;height: 1px;position: absolute;pointer-events: none;user-select: none;" id="svg-symbols" hidden aria-hidden role="presentation">
      <symbol id="symbol-chevron-down-14" width="14" height="14" viewBox="0 0 14 14">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M0.153392 5.58326L1.24661 4.21674L7 8.81945L12.7534 4.21674L13.8466 5.58326L7 11.0605L0.153392 5.58326Z" />
      </symbol>
      <symbol id="symbol-chevron-down-20" width="20" height="20" viewBox="0 0 20 20">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M0.219131 7.97608L1.78087 6.02391L10 12.5992L18.2191 6.02391L19.7809 7.97608L10 15.8008L0.219131 7.97608Z" />
      </symbol>
      <symbol id="symbol-chevron-left-14" width="14" height="14" viewBox="0 0 14 14">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M8.41674 0.153393L9.78326 1.24661L5.18055 7L9.78326 12.7534L8.41674 13.8466L2.93945 7L8.41674 0.153393Z" />
      </symbol>
      <symbol id="symbol-chevron-left-20" width="20" height="20" viewBox="0 0 20 20">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M12.0239 0.219131L13.9761 1.78087L7.40078 10L13.9761 18.2191L12.0239 19.7809L4.19922 10L12.0239 0.219131Z" />
      </symbol>
      <symbol id="symbol-chevron-right-14" width="14" height="14" viewBox="0 0 14 14">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M5.58326 0.153392L4.21674 1.24661L8.81945 7L4.21674 12.7534L5.58326 13.8466L11.0605 7L5.58326 0.153392Z" />
      </symbol>
      <symbol id="symbol-chevron-right-20" width="20" height="20" viewBox="0 0 20 20">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M7.97609 0.219116L6.02391 1.78085L12.5992 9.99998L6.02391 18.2191L7.97609 19.7809L15.8008 9.99998L7.97609 0.219116Z" />
      </symbol>
      <symbol id="symbol-chevron-up-14" width="14" height="14" viewBox="0 0 14 14">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M0.153393 8.45674L1.24661 9.82326L7 5.22055L12.7534 9.82326L13.8466 8.45674L7 2.97945L0.153393 8.45674Z" />
      </symbol>
      <symbol id="symbol-chevron-up-20" width="20" height="20" viewBox="0 0 20 20">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M0.219131 12.0239L1.78087 13.9761L10 7.40077L18.2191 13.9761L19.7809 12.0239L10 4.19921L0.219131 12.0239Z" />
      </symbol>
      <symbol id="symbol-close-14" width="14" height="14" viewBox="0 0 14 14">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M7 5.76257L11.2813 1.48129L12.5187 2.71872L8.23743 7.00001L12.5187 11.2813L11.2813 12.5187L7 8.23744L2.71872 12.5187L1.48129 11.2813L5.76256 7.00001L1.48128 2.71873L2.71872 1.48129L7 5.76257Z" />
      </symbol>
      <symbol id="symbol-close-20" width="20" height="20" viewBox="0 0 20 20">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M10 8.23224L16.1161 2.11612L17.8839 3.88389L11.7678 10L17.8839 16.1161L16.1161 17.8839L10 11.7678L3.88389 17.8839L2.11612 16.1161L8.23223 10L2.11612 3.88389L3.88388 2.11613L10 8.23224Z" />
      </symbol>
      <symbol id="symbol-search-14" width="14" height="14" viewBox="0 0 14 14">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M5.375 0C2.40647 0 0 2.40647 0 5.375C0 8.34353 2.40647 10.75 5.375 10.75C6.54342 10.75 7.62477 10.3772 8.50657 9.74401L12.375 13.6124L13.6124 12.375L9.74401 8.50657C10.3772 7.62477 10.75 6.54342 10.75 5.375C10.75 2.40647 8.34353 0 5.375 0ZM1.75 5.375C1.75 3.37297 3.37297 1.75 5.375 1.75C7.37703 1.75 9 3.37297 9 5.375C9 7.37703 7.37703 9 5.375 9C3.37297 9 1.75 7.37703 1.75 5.375Z" />
      </symbol>
      <symbol id="symbol-search-20" width="20" height="20" viewBox="0 0 20 20">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M7.67857 0.125C3.50685 0.125 0.125 3.50685 0.125 7.67857C0.125 11.8503 3.50685 15.2321 7.67857 15.2321C9.35859 15.2321 10.9105 14.6837 12.165 13.756L17.6786 19.2696L19.2696 17.6786L13.756 12.165C14.6837 10.9105 15.2321 9.35859 15.2321 7.67857C15.2321 3.50685 11.8503 0.125 7.67857 0.125ZM2.375 7.67857C2.375 4.74949 4.74949 2.375 7.67857 2.375C10.6077 2.375 12.9821 4.74949 12.9821 7.67857C12.9821 10.6077 10.6077 12.9821 7.67857 12.9821C4.74949 12.9821 2.375 10.6077 2.375 7.67857Z" />
      </symbol>
      <symbol id="symbol-chevron-left-14" width="14" height="14" viewBox="0 0 14 14">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M8.41674 0.153381L9.78326 1.2466L5.18055 6.99999L9.78326 12.7534L8.41674 13.8466L2.93945 6.99999L8.41674 0.153381Z" fill="white" />
      </symbol>
      <symbol id="symbol-chevron-right-14" width="14" height="14" viewBox="0 0 14 14">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M5.58332 13.8466L4.2168 12.7534L8.81951 6.99999L4.2168 1.2466L5.58332 0.153381L11.0606 6.99999L5.58332 13.8466Z" fill="white" />
      </symbol>
      <symbol id="symbol-fellow-1" width="12" height="12" viewBox="0 0 12 12">
            <circle cx="6" cy="6" r="6" fill="#3A8138" />
            <path d="M2.50098 10.8747C3.1582 9.5653 4.5132 8.66667 6.07809 8.66667C7.60953 8.66667 8.93996 9.5273 9.61211 10.7913C8.60725 11.5501 7.35615 12 5.99996 12C4.69423 12 3.48591 11.5829 2.50098 10.8747Z" fill="#f1f1f1" />
            <path d="M8.21123 6C8.21123 7.10457 7.3158 8 6.21123 8C5.10666 8 4.21123 7.10457 4.21123 6C4.21123 4.89543 5.10666 4 6.21123 4C7.3158 4 8.21123 4.89543 8.21123 6Z" fill="#f1f1f1" />
      </symbol>
      <symbol id="symbol-fellow-2" width="20" height="12" viewBox="0 0 20 12">
            <path d="M6.25 0C7.17064 0 8.04284 0.20735 8.82247 0.577895C7.25237 1.86156 6.25012 3.81372 6.25012 5.99997C6.25012 8.18624 7.25239 10.1384 8.82252 11.4221C8.04288 11.7926 7.17066 12 6.25 12C2.93629 12 0.25 9.31371 0.25 6C0.25 2.68629 2.93629 0 6.25 0Z" fill="#3A8138" />
            <path d="M6.53951 4.0015C6.51354 4.0005 6.48745 4 6.46123 4C5.35666 4 4.46123 4.89543 4.46123 6C4.46123 7.10457 5.35666 8 6.46123 8C6.48745 8 6.51355 7.9995 6.53953 7.9985C6.35121 7.36522 6.25009 6.6944 6.25009 5.99997C6.25009 5.30556 6.3512 4.63476 6.53951 4.0015Z" fill="#F1F1F1" />
            <path d="M6.78674 8.69268C6.63624 8.6755 6.4832 8.66667 6.32809 8.66667C4.7632 8.66667 3.4082 9.5653 2.75098 10.8747C3.73591 11.5829 4.94423 12 6.24997 12C7.17062 12 8.04285 11.7926 8.82249 11.4221C7.93764 10.6987 7.23315 9.76295 6.78674 8.69268Z" fill="#F1F1F1" />
            <circle cx="14" cy="6" r="6" fill="#3A8138" />
            <path d="M10.501 10.8747C11.1582 9.5653 12.5132 8.66667 14.0781 8.66667C15.6095 8.66667 16.94 9.5273 17.6121 10.7913C16.6072 11.5501 15.3562 12 14 12C12.6942 12 11.4859 11.5829 10.501 10.8747Z" fill="#f1f1f1" />
            <path d="M16.2112 6C16.2112 7.10457 15.3158 8 14.2112 8C13.1067 8 12.2112 7.10457 12.2112 6C12.2112 4.89543 13.1067 4 14.2112 4C15.3158 4 16.2112 4.89543 16.2112 6Z" fill="#f1f1f1" />
      </symbol>
      <symbol id="symbol-fellow-3" width="28" height="12" viewBox="0 0 28 12">
            <path d="M6.25 0C7.17064 0 8.04284 0.20735 8.82247 0.577895C7.25237 1.86156 6.25012 3.81372 6.25012 5.99997C6.25012 8.18624 7.25239 10.1384 8.82252 11.4221C8.04288 11.7926 7.17066 12 6.25 12C2.93629 12 0.25 9.31371 0.25 6C0.25 2.68629 2.93629 0 6.25 0Z" fill="#3A8138" />
            <path d="M6.53951 4.0015C6.51354 4.0005 6.48745 4 6.46123 4C5.35666 4 4.46123 4.89543 4.46123 6C4.46123 7.10457 5.35666 8 6.46123 8C6.48745 8 6.51355 7.9995 6.53953 7.9985C6.35121 7.36522 6.25009 6.6944 6.25009 5.99997C6.25009 5.30556 6.3512 4.63476 6.53951 4.0015Z" fill="#F1F1F1" />
            <path d="M6.78674 8.69268C6.63624 8.6755 6.4832 8.66667 6.32809 8.66667C4.7632 8.66667 3.4082 9.5653 2.75098 10.8747C3.73591 11.5829 4.94423 12 6.24997 12C7.17062 12 8.04285 11.7926 8.82249 11.4221C7.93764 10.6987 7.23315 9.76295 6.78674 8.69268Z" fill="#F1F1F1" />
            <path d="M14.25 0C15.1706 0 16.0428 0.20735 16.8225 0.577895C15.2524 1.86156 14.2501 3.81372 14.2501 5.99997C14.2501 8.18624 15.2524 10.1384 16.8225 11.4221C16.0429 11.7926 15.1707 12 14.25 12C10.9363 12 8.25 9.31371 8.25 6C8.25 2.68629 10.9363 0 14.25 0Z" fill="#3A8138" />
            <path d="M14.5395 4.0015C14.5135 4.0005 14.4874 4 14.4612 4C13.3567 4 12.4612 4.89543 12.4612 6C12.4612 7.10457 13.3567 8 14.4612 8C14.4875 8 14.5136 7.9995 14.5395 7.9985C14.3512 7.36522 14.2501 6.6944 14.2501 5.99997C14.2501 5.30556 14.3512 4.63476 14.5395 4.0015Z" fill="#F1F1F1" />
            <path d="M14.7867 8.69268C14.6362 8.6755 14.4832 8.66667 14.3281 8.66667C12.7632 8.66667 11.4082 9.5653 10.751 10.8747C11.7359 11.5829 12.9442 12 14.25 12C15.1706 12 16.0428 11.7926 16.8225 11.4221C15.9376 10.6987 15.2331 9.76295 14.7867 8.69268Z" fill="#F1F1F1" />
            <circle cx="22" cy="6" r="6" fill="#3A8138" />
            <path d="M18.501 10.8747C19.1582 9.5653 20.5132 8.66667 22.0781 8.66667C23.6095 8.66667 24.94 9.5273 25.6121 10.7913C24.6072 11.5501 23.3562 12 22 12C20.6942 12 19.4859 11.5829 18.501 10.8747Z" fill="#f1f1f1" />
            <path d="M24.2112 6C24.2112 7.10457 23.3158 8 22.2112 8C21.1067 8 20.2112 7.10457 20.2112 6C20.2112 4.89543 21.1067 4 22.2112 4C23.3158 4 24.2112 4.89543 24.2112 6Z" fill="#f1f1f1" />
      </symbol>
      <symbol id="symbol-fellow-4" width="36" height="12" viewBox="0 0 36 12">
            <path d="M6.25 0C7.17064 0 8.04284 0.20735 8.82247 0.577895C7.25237 1.86156 6.25012 3.81372 6.25012 5.99997C6.25012 8.18624 7.25239 10.1384 8.82252 11.4221C8.04288 11.7926 7.17066 12 6.25 12C2.93629 12 0.25 9.31371 0.25 6C0.25 2.68629 2.93629 0 6.25 0Z" fill="#3A8138" />
            <path d="M6.53951 4.0015C6.51354 4.0005 6.48745 4 6.46123 4C5.35666 4 4.46123 4.89543 4.46123 6C4.46123 7.10457 5.35666 8 6.46123 8C6.48745 8 6.51355 7.9995 6.53953 7.9985C6.35121 7.36522 6.25009 6.6944 6.25009 5.99997C6.25009 5.30556 6.3512 4.63476 6.53951 4.0015Z" fill="#F1F1F1" />
            <path d="M6.78674 8.69268C6.63624 8.6755 6.4832 8.66667 6.32809 8.66667C4.7632 8.66667 3.4082 9.5653 2.75098 10.8747C3.73591 11.5829 4.94423 12 6.24997 12C7.17062 12 8.04285 11.7926 8.82249 11.4221C7.93764 10.6987 7.23315 9.76295 6.78674 8.69268Z" fill="#F1F1F1" />
            <path d="M14.25 0C15.1706 0 16.0428 0.20735 16.8225 0.577895C15.2524 1.86156 14.2501 3.81372 14.2501 5.99997C14.2501 8.18624 15.2524 10.1384 16.8225 11.4221C16.0429 11.7926 15.1707 12 14.25 12C10.9363 12 8.25 9.31371 8.25 6C8.25 2.68629 10.9363 0 14.25 0Z" fill="#3A8138" />
            <path d="M14.5395 4.0015C14.5135 4.0005 14.4874 4 14.4612 4C13.3567 4 12.4612 4.89543 12.4612 6C12.4612 7.10457 13.3567 8 14.4612 8C14.4875 8 14.5136 7.9995 14.5395 7.9985C14.3512 7.36522 14.2501 6.6944 14.2501 5.99997C14.2501 5.30556 14.3512 4.63476 14.5395 4.0015Z" fill="#F1F1F1" />
            <path d="M14.7867 8.69268C14.6362 8.6755 14.4832 8.66667 14.3281 8.66667C12.7632 8.66667 11.4082 9.5653 10.751 10.8747C11.7359 11.5829 12.9442 12 14.25 12C15.1706 12 16.0428 11.7926 16.8225 11.4221C15.9376 10.6987 15.2331 9.76295 14.7867 8.69268Z" fill="#F1F1F1" />
            <path d="M22.25 0C23.1706 0 24.0428 0.20735 24.8225 0.577895C23.2524 1.86156 22.2501 3.81372 22.2501 5.99997C22.2501 8.18624 23.2524 10.1384 24.8225 11.4221C24.0429 11.7926 23.1707 12 22.25 12C18.9363 12 16.25 9.31371 16.25 6C16.25 2.68629 18.9363 0 22.25 0Z" fill="#3A8138" />
            <path d="M22.5395 4.0015C22.5135 4.0005 22.4874 4 22.4612 4C21.3567 4 20.4612 4.89543 20.4612 6C20.4612 7.10457 21.3567 8 22.4612 8C22.4875 8 22.5136 7.9995 22.5395 7.9985C22.3512 7.36522 22.2501 6.6944 22.2501 5.99997C22.2501 5.30556 22.3512 4.63476 22.5395 4.0015Z" fill="#F1F1F1" />
            <path d="M22.7867 8.69268C22.6362 8.6755 22.4832 8.66667 22.3281 8.66667C20.7632 8.66667 19.4082 9.5653 18.751 10.8747C19.7359 11.5829 20.9442 12 22.25 12C23.1706 12 24.0428 11.7926 24.8225 11.4221C23.9376 10.6987 23.2331 9.76295 22.7867 8.69268Z" fill="#F1F1F1" />
            <circle cx="30" cy="6" r="6" fill="#3A8138" />
            <path d="M26.501 10.8747C27.1582 9.5653 28.5132 8.66667 30.0781 8.66667C31.6095 8.66667 32.94 9.5273 33.6121 10.7913C32.6072 11.5501 31.3562 12 30 12C28.6942 12 27.4859 11.5829 26.501 10.8747Z" fill="#f1f1f1" />
            <path d="M32.2112 6C32.2112 7.10457 31.3158 8 30.2112 8C29.1067 8 28.2112 7.10457 28.2112 6C28.2112 4.89543 29.1067 4 30.2112 4C31.3158 4 32.2112 4.89543 32.2112 6Z" fill="#f1f1f1" />
      </symbol>
      <symbol id="symbol-fellow-5" width="44" height="12" viewBox="0 0 44 12">
            <path d="M6.25 0C7.17064 0 8.04284 0.20735 8.82247 0.577895C7.25237 1.86156 6.25012 3.81372 6.25012 5.99997C6.25012 8.18624 7.25239 10.1384 8.82252 11.4221C8.04288 11.7926 7.17066 12 6.25 12C2.93629 12 0.25 9.31371 0.25 6C0.25 2.68629 2.93629 0 6.25 0Z" fill="#3A8138" />
            <path d="M6.53951 4.0015C6.51354 4.0005 6.48745 4 6.46123 4C5.35666 4 4.46123 4.89543 4.46123 6C4.46123 7.10457 5.35666 8 6.46123 8C6.48745 8 6.51355 7.9995 6.53953 7.9985C6.35121 7.36522 6.25009 6.6944 6.25009 5.99997C6.25009 5.30556 6.3512 4.63476 6.53951 4.0015Z" fill="#F1F1F1" />
            <path d="M6.78674 8.69268C6.63624 8.6755 6.4832 8.66667 6.32809 8.66667C4.7632 8.66667 3.4082 9.5653 2.75098 10.8747C3.73591 11.5829 4.94423 12 6.24997 12C7.17062 12 8.04285 11.7926 8.82249 11.4221C7.93764 10.6987 7.23315 9.76295 6.78674 8.69268Z" fill="#F1F1F1" />
            <path d="M14.25 0C15.1706 0 16.0428 0.20735 16.8225 0.577895C15.2524 1.86156 14.2501 3.81372 14.2501 5.99997C14.2501 8.18624 15.2524 10.1384 16.8225 11.4221C16.0429 11.7926 15.1707 12 14.25 12C10.9363 12 8.25 9.31371 8.25 6C8.25 2.68629 10.9363 0 14.25 0Z" fill="#3A8138" />
            <path d="M14.5395 4.0015C14.5135 4.0005 14.4874 4 14.4612 4C13.3567 4 12.4612 4.89543 12.4612 6C12.4612 7.10457 13.3567 8 14.4612 8C14.4875 8 14.5136 7.9995 14.5395 7.9985C14.3512 7.36522 14.2501 6.6944 14.2501 5.99997C14.2501 5.30556 14.3512 4.63476 14.5395 4.0015Z" fill="#F1F1F1" />
            <path d="M14.7867 8.69268C14.6362 8.6755 14.4832 8.66667 14.3281 8.66667C12.7632 8.66667 11.4082 9.5653 10.751 10.8747C11.7359 11.5829 12.9442 12 14.25 12C15.1706 12 16.0428 11.7926 16.8225 11.4221C15.9376 10.6987 15.2331 9.76295 14.7867 8.69268Z" fill="#F1F1F1" />
            <path d="M22.25 0C23.1706 0 24.0428 0.20735 24.8225 0.577895C23.2524 1.86156 22.2501 3.81372 22.2501 5.99997C22.2501 8.18624 23.2524 10.1384 24.8225 11.4221C24.0429 11.7926 23.1707 12 22.25 12C18.9363 12 16.25 9.31371 16.25 6C16.25 2.68629 18.9363 0 22.25 0Z" fill="#3A8138" />
            <path d="M22.5395 4.0015C22.5135 4.0005 22.4874 4 22.4612 4C21.3567 4 20.4612 4.89543 20.4612 6C20.4612 7.10457 21.3567 8 22.4612 8C22.4875 8 22.5136 7.9995 22.5395 7.9985C22.3512 7.36522 22.2501 6.6944 22.2501 5.99997C22.2501 5.30556 22.3512 4.63476 22.5395 4.0015Z" fill="#F1F1F1" />
            <path d="M22.7867 8.69268C22.6362 8.6755 22.4832 8.66667 22.3281 8.66667C20.7632 8.66667 19.4082 9.5653 18.751 10.8747C19.7359 11.5829 20.9442 12 22.25 12C23.1706 12 24.0428 11.7926 24.8225 11.4221C23.9376 10.6987 23.2331 9.76295 22.7867 8.69268Z" fill="#F1F1F1" />
            <path d="M30.25 0C31.1706 0 32.0428 0.20735 32.8225 0.577895C31.2524 1.86156 30.2501 3.81372 30.2501 5.99997C30.2501 8.18624 31.2524 10.1384 32.8225 11.4221C32.0429 11.7926 31.1707 12 30.25 12C26.9363 12 24.25 9.31371 24.25 6C24.25 2.68629 26.9363 0 30.25 0Z" fill="#3A8138" />
            <path d="M30.5395 4.0015C30.5135 4.0005 30.4874 4 30.4612 4C29.3567 4 28.4612 4.89543 28.4612 6C28.4612 7.10457 29.3567 8 30.4612 8C30.4875 8 30.5136 7.9995 30.5395 7.9985C30.3512 7.36522 30.2501 6.6944 30.2501 5.99997C30.2501 5.30556 30.3512 4.63476 30.5395 4.0015Z" fill="#F1F1F1" />
            <path d="M30.7867 8.69268C30.6362 8.6755 30.4832 8.66667 30.3281 8.66667C28.7632 8.66667 27.4082 9.5653 26.751 10.8747C27.7359 11.5829 28.9442 12 30.25 12C31.1706 12 32.0428 11.7926 32.8225 11.4221C31.9376 10.6987 31.2331 9.76295 30.7867 8.69268Z" fill="#F1F1F1" />
            <circle cx="38" cy="6" r="6" fill="#3A8138" />
            <path d="M34.501 10.8747C35.1582 9.5653 36.5132 8.66667 38.0781 8.66667C39.6095 8.66667 40.94 9.5273 41.6121 10.7913C40.6072 11.5501 39.3562 12 38 12C36.6942 12 35.4859 11.5829 34.501 10.8747Z" fill="#f1f1f1" />
            <path d="M40.2112 6C40.2112 7.10457 39.3158 8 38.2112 8C37.1067 8 36.2112 7.10457 36.2112 6C36.2112 4.89543 37.1067 4 38.2112 4C39.3158 4 40.2112 4.89543 40.2112 6Z" fill="#f1f1f1" />
      </symbol>
      <symbol id="symbol-fellow-6" width="52" height="12" viewBox="0 0 52 12">
            <path d="M6.25 0C7.17064 0 8.04284 0.20735 8.82247 0.577895C7.25237 1.86156 6.25012 3.81372 6.25012 5.99997C6.25012 8.18624 7.25239 10.1384 8.82252 11.4221C8.04288 11.7926 7.17066 12 6.25 12C2.93629 12 0.25 9.31371 0.25 6C0.25 2.68629 2.93629 0 6.25 0Z" fill="#3A8138" />
            <path d="M6.53951 4.0015C6.51354 4.0005 6.48745 4 6.46123 4C5.35666 4 4.46123 4.89543 4.46123 6C4.46123 7.10457 5.35666 8 6.46123 8C6.48745 8 6.51355 7.9995 6.53953 7.9985C6.35121 7.36522 6.25009 6.6944 6.25009 5.99997C6.25009 5.30556 6.3512 4.63476 6.53951 4.0015Z" fill="#F1F1F1" />
            <path d="M6.78674 8.69268C6.63624 8.6755 6.4832 8.66667 6.32809 8.66667C4.7632 8.66667 3.4082 9.5653 2.75098 10.8747C3.73591 11.5829 4.94423 12 6.24997 12C7.17062 12 8.04285 11.7926 8.82249 11.4221C7.93764 10.6987 7.23315 9.76295 6.78674 8.69268Z" fill="#F1F1F1" />
            <path d="M14.25 0C15.1706 0 16.0428 0.20735 16.8225 0.577895C15.2524 1.86156 14.2501 3.81372 14.2501 5.99997C14.2501 8.18624 15.2524 10.1384 16.8225 11.4221C16.0429 11.7926 15.1707 12 14.25 12C10.9363 12 8.25 9.31371 8.25 6C8.25 2.68629 10.9363 0 14.25 0Z" fill="#3A8138" />
            <path d="M14.5395 4.0015C14.5135 4.0005 14.4874 4 14.4612 4C13.3567 4 12.4612 4.89543 12.4612 6C12.4612 7.10457 13.3567 8 14.4612 8C14.4875 8 14.5136 7.9995 14.5395 7.9985C14.3512 7.36522 14.2501 6.6944 14.2501 5.99997C14.2501 5.30556 14.3512 4.63476 14.5395 4.0015Z" fill="#F1F1F1" />
            <path d="M14.7867 8.69268C14.6362 8.6755 14.4832 8.66667 14.3281 8.66667C12.7632 8.66667 11.4082 9.5653 10.751 10.8747C11.7359 11.5829 12.9442 12 14.25 12C15.1706 12 16.0428 11.7926 16.8225 11.4221C15.9376 10.6987 15.2331 9.76295 14.7867 8.69268Z" fill="#F1F1F1" />
            <path d="M22.25 0C23.1706 0 24.0428 0.20735 24.8225 0.577895C23.2524 1.86156 22.2501 3.81372 22.2501 5.99997C22.2501 8.18624 23.2524 10.1384 24.8225 11.4221C24.0429 11.7926 23.1707 12 22.25 12C18.9363 12 16.25 9.31371 16.25 6C16.25 2.68629 18.9363 0 22.25 0Z" fill="#3A8138" />
            <path d="M22.5395 4.0015C22.5135 4.0005 22.4874 4 22.4612 4C21.3567 4 20.4612 4.89543 20.4612 6C20.4612 7.10457 21.3567 8 22.4612 8C22.4875 8 22.5136 7.9995 22.5395 7.9985C22.3512 7.36522 22.2501 6.6944 22.2501 5.99997C22.2501 5.30556 22.3512 4.63476 22.5395 4.0015Z" fill="#F1F1F1" />
            <path d="M22.7867 8.69268C22.6362 8.6755 22.4832 8.66667 22.3281 8.66667C20.7632 8.66667 19.4082 9.5653 18.751 10.8747C19.7359 11.5829 20.9442 12 22.25 12C23.1706 12 24.0428 11.7926 24.8225 11.4221C23.9376 10.6987 23.2331 9.76295 22.7867 8.69268Z" fill="#F1F1F1" />
            <path d="M30.25 0C31.1706 0 32.0428 0.20735 32.8225 0.577895C31.2524 1.86156 30.2501 3.81372 30.2501 5.99997C30.2501 8.18624 31.2524 10.1384 32.8225 11.4221C32.0429 11.7926 31.1707 12 30.25 12C26.9363 12 24.25 9.31371 24.25 6C24.25 2.68629 26.9363 0 30.25 0Z" fill="#3A8138" />
            <path d="M30.5395 4.0015C30.5135 4.0005 30.4874 4 30.4612 4C29.3567 4 28.4612 4.89543 28.4612 6C28.4612 7.10457 29.3567 8 30.4612 8C30.4875 8 30.5136 7.9995 30.5395 7.9985C30.3512 7.36522 30.2501 6.6944 30.2501 5.99997C30.2501 5.30556 30.3512 4.63476 30.5395 4.0015Z" fill="#F1F1F1" />
            <path d="M30.7867 8.69268C30.6362 8.6755 30.4832 8.66667 30.3281 8.66667C28.7632 8.66667 27.4082 9.5653 26.751 10.8747C27.7359 11.5829 28.9442 12 30.25 12C31.1706 12 32.0428 11.7926 32.8225 11.4221C31.9376 10.6987 31.2331 9.76295 30.7867 8.69268Z" fill="#F1F1F1" />
            <path d="M38.25 0C39.1706 0 40.0428 0.20735 40.8225 0.577895C39.2524 1.86156 38.2501 3.81372 38.2501 5.99997C38.2501 8.18624 39.2524 10.1384 40.8225 11.4221C40.0429 11.7926 39.1707 12 38.25 12C34.9363 12 32.25 9.31371 32.25 6C32.25 2.68629 34.9363 0 38.25 0Z" fill="#3A8138" />
            <path d="M38.5395 4.0015C38.5135 4.0005 38.4874 4 38.4612 4C37.3567 4 36.4612 4.89543 36.4612 6C36.4612 7.10457 37.3567 8 38.4612 8C38.4875 8 38.5136 7.9995 38.5395 7.9985C38.3512 7.36522 38.2501 6.6944 38.2501 5.99997C38.2501 5.30556 38.3512 4.63476 38.5395 4.0015Z" fill="#F1F1F1" />
            <path d="M38.7867 8.69268C38.6362 8.6755 38.4832 8.66667 38.3281 8.66667C36.7632 8.66667 35.4082 9.5653 34.751 10.8747C35.7359 11.5829 36.9442 12 38.25 12C39.1706 12 40.0428 11.7926 40.8225 11.4221C39.9376 10.6987 39.2331 9.76295 38.7867 8.69268Z" fill="#F1F1F1" />
            <circle cx="46" cy="6" r="6" fill="#3A8138" />
            <path d="M42.501 10.8747C43.1582 9.5653 44.5132 8.66667 46.0781 8.66667C47.6095 8.66667 48.94 9.5273 49.6121 10.7913C48.6072 11.5501 47.3562 12 46 12C44.6942 12 43.4859 11.5829 42.501 10.8747Z" fill="#f1f1f1" />
            <path d="M48.2112 6C48.2112 7.10457 47.3158 8 46.2112 8C45.1067 8 44.2112 7.10457 44.2112 6C44.2112 4.89543 45.1067 4 46.2112 4C47.3158 4 48.2112 4.89543 48.2112 6Z" fill="#f1f1f1" />
      </symbol>
      <symbol id="symbol-fellow-7" width="60" height="12" viewBox="0 0 60 12">
            <path d="M6.25 0C7.17064 0 8.04284 0.20735 8.82247 0.577895C7.25237 1.86156 6.25012 3.81372 6.25012 5.99997C6.25012 8.18624 7.25239 10.1384 8.82252 11.4221C8.04288 11.7926 7.17066 12 6.25 12C2.93629 12 0.25 9.31371 0.25 6C0.25 2.68629 2.93629 0 6.25 0Z" fill="#3A8138" />
            <path d="M6.53951 4.0015C6.51354 4.0005 6.48745 4 6.46123 4C5.35666 4 4.46123 4.89543 4.46123 6C4.46123 7.10457 5.35666 8 6.46123 8C6.48745 8 6.51355 7.9995 6.53953 7.9985C6.35121 7.36522 6.25009 6.6944 6.25009 5.99997C6.25009 5.30556 6.3512 4.63476 6.53951 4.0015Z" fill="#F1F1F1" />
            <path d="M6.78674 8.69268C6.63624 8.6755 6.4832 8.66667 6.32809 8.66667C4.7632 8.66667 3.4082 9.5653 2.75098 10.8747C3.73591 11.5829 4.94423 12 6.24997 12C7.17062 12 8.04285 11.7926 8.82249 11.4221C7.93764 10.6987 7.23315 9.76295 6.78674 8.69268Z" fill="#F1F1F1" />
            <path d="M14.25 0C15.1706 0 16.0428 0.20735 16.8225 0.577895C15.2524 1.86156 14.2501 3.81372 14.2501 5.99997C14.2501 8.18624 15.2524 10.1384 16.8225 11.4221C16.0429 11.7926 15.1707 12 14.25 12C10.9363 12 8.25 9.31371 8.25 6C8.25 2.68629 10.9363 0 14.25 0Z" fill="#3A8138" />
            <path d="M14.5395 4.0015C14.5135 4.0005 14.4874 4 14.4612 4C13.3567 4 12.4612 4.89543 12.4612 6C12.4612 7.10457 13.3567 8 14.4612 8C14.4875 8 14.5136 7.9995 14.5395 7.9985C14.3512 7.36522 14.2501 6.6944 14.2501 5.99997C14.2501 5.30556 14.3512 4.63476 14.5395 4.0015Z" fill="#F1F1F1" />
            <path d="M14.7867 8.69268C14.6362 8.6755 14.4832 8.66667 14.3281 8.66667C12.7632 8.66667 11.4082 9.5653 10.751 10.8747C11.7359 11.5829 12.9442 12 14.25 12C15.1706 12 16.0428 11.7926 16.8225 11.4221C15.9376 10.6987 15.2331 9.76295 14.7867 8.69268Z" fill="#F1F1F1" />
            <path d="M22.25 0C23.1706 0 24.0428 0.20735 24.8225 0.577895C23.2524 1.86156 22.2501 3.81372 22.2501 5.99997C22.2501 8.18624 23.2524 10.1384 24.8225 11.4221C24.0429 11.7926 23.1707 12 22.25 12C18.9363 12 16.25 9.31371 16.25 6C16.25 2.68629 18.9363 0 22.25 0Z" fill="#3A8138" />
            <path d="M22.5395 4.0015C22.5135 4.0005 22.4874 4 22.4612 4C21.3567 4 20.4612 4.89543 20.4612 6C20.4612 7.10457 21.3567 8 22.4612 8C22.4875 8 22.5136 7.9995 22.5395 7.9985C22.3512 7.36522 22.2501 6.6944 22.2501 5.99997C22.2501 5.30556 22.3512 4.63476 22.5395 4.0015Z" fill="#F1F1F1" />
            <path d="M22.7867 8.69268C22.6362 8.6755 22.4832 8.66667 22.3281 8.66667C20.7632 8.66667 19.4082 9.5653 18.751 10.8747C19.7359 11.5829 20.9442 12 22.25 12C23.1706 12 24.0428 11.7926 24.8225 11.4221C23.9376 10.6987 23.2331 9.76295 22.7867 8.69268Z" fill="#F1F1F1" />
            <path d="M30.25 0C31.1706 0 32.0428 0.20735 32.8225 0.577895C31.2524 1.86156 30.2501 3.81372 30.2501 5.99997C30.2501 8.18624 31.2524 10.1384 32.8225 11.4221C32.0429 11.7926 31.1707 12 30.25 12C26.9363 12 24.25 9.31371 24.25 6C24.25 2.68629 26.9363 0 30.25 0Z" fill="#3A8138" />
            <path d="M30.5395 4.0015C30.5135 4.0005 30.4874 4 30.4612 4C29.3567 4 28.4612 4.89543 28.4612 6C28.4612 7.10457 29.3567 8 30.4612 8C30.4875 8 30.5136 7.9995 30.5395 7.9985C30.3512 7.36522 30.2501 6.6944 30.2501 5.99997C30.2501 5.30556 30.3512 4.63476 30.5395 4.0015Z" fill="#F1F1F1" />
            <path d="M30.7867 8.69268C30.6362 8.6755 30.4832 8.66667 30.3281 8.66667C28.7632 8.66667 27.4082 9.5653 26.751 10.8747C27.7359 11.5829 28.9442 12 30.25 12C31.1706 12 32.0428 11.7926 32.8225 11.4221C31.9376 10.6987 31.2331 9.76295 30.7867 8.69268Z" fill="#F1F1F1" />
            <path d="M38.25 0C39.1706 0 40.0428 0.20735 40.8225 0.577895C39.2524 1.86156 38.2501 3.81372 38.2501 5.99997C38.2501 8.18624 39.2524 10.1384 40.8225 11.4221C40.0429 11.7926 39.1707 12 38.25 12C34.9363 12 32.25 9.31371 32.25 6C32.25 2.68629 34.9363 0 38.25 0Z" fill="#3A8138" />
            <path d="M38.5395 4.0015C38.5135 4.0005 38.4874 4 38.4612 4C37.3567 4 36.4612 4.89543 36.4612 6C36.4612 7.10457 37.3567 8 38.4612 8C38.4875 8 38.5136 7.9995 38.5395 7.9985C38.3512 7.36522 38.2501 6.6944 38.2501 5.99997C38.2501 5.30556 38.3512 4.63476 38.5395 4.0015Z" fill="#F1F1F1" />
            <path d="M38.7867 8.69268C38.6362 8.6755 38.4832 8.66667 38.3281 8.66667C36.7632 8.66667 35.4082 9.5653 34.751 10.8747C35.7359 11.5829 36.9442 12 38.25 12C39.1706 12 40.0428 11.7926 40.8225 11.4221C39.9376 10.6987 39.2331 9.76295 38.7867 8.69268Z" fill="#F1F1F1" />
            <path d="M46.25 0C47.1706 0 48.0428 0.20735 48.8225 0.577895C47.2524 1.86156 46.2501 3.81372 46.2501 5.99997C46.2501 8.18624 47.2524 10.1384 48.8225 11.4221C48.0429 11.7926 47.1707 12 46.25 12C42.9363 12 40.25 9.31371 40.25 6C40.25 2.68629 42.9363 0 46.25 0Z" fill="#3A8138" />
            <path d="M46.5395 4.0015C46.5135 4.0005 46.4874 4 46.4612 4C45.3567 4 44.4612 4.89543 44.4612 6C44.4612 7.10457 45.3567 8 46.4612 8C46.4875 8 46.5136 7.9995 46.5395 7.9985C46.3512 7.36522 46.2501 6.6944 46.2501 5.99997C46.2501 5.30556 46.3512 4.63476 46.5395 4.0015Z" fill="#F1F1F1" />
            <path d="M46.7867 8.69268C46.6362 8.6755 46.4832 8.66667 46.3281 8.66667C44.7632 8.66667 43.4082 9.5653 42.751 10.8747C43.7359 11.5829 44.9442 12 46.25 12C47.1706 12 48.0428 11.7926 48.8225 11.4221C47.9376 10.6987 47.2331 9.76295 46.7867 8.69268Z" fill="#F1F1F1" />
            <circle cx="54" cy="6" r="6" fill="#3A8138" />
            <path d="M50.501 10.8747C51.1582 9.5653 52.5132 8.66667 54.0781 8.66667C55.6095 8.66667 56.94 9.5273 57.6121 10.7913C56.6072 11.5501 55.3562 12 54 12C52.6942 12 51.4859 11.5829 50.501 10.8747Z" fill="#f1f1f1" />
            <path d="M56.2112 6C56.2112 7.10457 55.3158 8 54.2112 8C53.1067 8 52.2112 7.10457 52.2112 6C52.2112 4.89543 53.1067 4 54.2112 4C55.3158 4 56.2112 4.89543 56.2112 6Z" fill="#f1f1f1" />
      </symbol>
      <symbol id="symbol-fellow-8" width="68" height="12" viewBox="0 0 68 12">
            <path d="M6.25 0C7.17064 0 8.04284 0.20735 8.82247 0.577895C7.25237 1.86156 6.25012 3.81372 6.25012 5.99997C6.25012 8.18624 7.25239 10.1384 8.82252 11.4221C8.04288 11.7926 7.17066 12 6.25 12C2.93629 12 0.25 9.31371 0.25 6C0.25 2.68629 2.93629 0 6.25 0Z" fill="#3A8138" />
            <path d="M6.53951 4.0015C6.51354 4.0005 6.48745 4 6.46123 4C5.35666 4 4.46123 4.89543 4.46123 6C4.46123 7.10457 5.35666 8 6.46123 8C6.48745 8 6.51355 7.9995 6.53953 7.9985C6.35121 7.36522 6.25009 6.6944 6.25009 5.99997C6.25009 5.30556 6.3512 4.63476 6.53951 4.0015Z" fill="#F1F1F1" />
            <path d="M6.78674 8.69268C6.63624 8.6755 6.4832 8.66667 6.32809 8.66667C4.7632 8.66667 3.4082 9.5653 2.75098 10.8747C3.73591 11.5829 4.94423 12 6.24997 12C7.17062 12 8.04285 11.7926 8.82249 11.4221C7.93764 10.6987 7.23315 9.76295 6.78674 8.69268Z" fill="#F1F1F1" />
            <path d="M14.25 0C15.1706 0 16.0428 0.20735 16.8225 0.577895C15.2524 1.86156 14.2501 3.81372 14.2501 5.99997C14.2501 8.18624 15.2524 10.1384 16.8225 11.4221C16.0429 11.7926 15.1707 12 14.25 12C10.9363 12 8.25 9.31371 8.25 6C8.25 2.68629 10.9363 0 14.25 0Z" fill="#3A8138" />
            <path d="M14.5395 4.0015C14.5135 4.0005 14.4874 4 14.4612 4C13.3567 4 12.4612 4.89543 12.4612 6C12.4612 7.10457 13.3567 8 14.4612 8C14.4875 8 14.5136 7.9995 14.5395 7.9985C14.3512 7.36522 14.2501 6.6944 14.2501 5.99997C14.2501 5.30556 14.3512 4.63476 14.5395 4.0015Z" fill="#F1F1F1" />
            <path d="M14.7867 8.69268C14.6362 8.6755 14.4832 8.66667 14.3281 8.66667C12.7632 8.66667 11.4082 9.5653 10.751 10.8747C11.7359 11.5829 12.9442 12 14.25 12C15.1706 12 16.0428 11.7926 16.8225 11.4221C15.9376 10.6987 15.2331 9.76295 14.7867 8.69268Z" fill="#F1F1F1" />
            <path d="M22.25 0C23.1706 0 24.0428 0.20735 24.8225 0.577895C23.2524 1.86156 22.2501 3.81372 22.2501 5.99997C22.2501 8.18624 23.2524 10.1384 24.8225 11.4221C24.0429 11.7926 23.1707 12 22.25 12C18.9363 12 16.25 9.31371 16.25 6C16.25 2.68629 18.9363 0 22.25 0Z" fill="#3A8138" />
            <path d="M22.5395 4.0015C22.5135 4.0005 22.4874 4 22.4612 4C21.3567 4 20.4612 4.89543 20.4612 6C20.4612 7.10457 21.3567 8 22.4612 8C22.4875 8 22.5136 7.9995 22.5395 7.9985C22.3512 7.36522 22.2501 6.6944 22.2501 5.99997C22.2501 5.30556 22.3512 4.63476 22.5395 4.0015Z" fill="#F1F1F1" />
            <path d="M22.7867 8.69268C22.6362 8.6755 22.4832 8.66667 22.3281 8.66667C20.7632 8.66667 19.4082 9.5653 18.751 10.8747C19.7359 11.5829 20.9442 12 22.25 12C23.1706 12 24.0428 11.7926 24.8225 11.4221C23.9376 10.6987 23.2331 9.76295 22.7867 8.69268Z" fill="#F1F1F1" />
            <path d="M30.25 0C31.1706 0 32.0428 0.20735 32.8225 0.577895C31.2524 1.86156 30.2501 3.81372 30.2501 5.99997C30.2501 8.18624 31.2524 10.1384 32.8225 11.4221C32.0429 11.7926 31.1707 12 30.25 12C26.9363 12 24.25 9.31371 24.25 6C24.25 2.68629 26.9363 0 30.25 0Z" fill="#3A8138" />
            <path d="M30.5395 4.0015C30.5135 4.0005 30.4874 4 30.4612 4C29.3567 4 28.4612 4.89543 28.4612 6C28.4612 7.10457 29.3567 8 30.4612 8C30.4875 8 30.5136 7.9995 30.5395 7.9985C30.3512 7.36522 30.2501 6.6944 30.2501 5.99997C30.2501 5.30556 30.3512 4.63476 30.5395 4.0015Z" fill="#F1F1F1" />
            <path d="M30.7867 8.69268C30.6362 8.6755 30.4832 8.66667 30.3281 8.66667C28.7632 8.66667 27.4082 9.5653 26.751 10.8747C27.7359 11.5829 28.9442 12 30.25 12C31.1706 12 32.0428 11.7926 32.8225 11.4221C31.9376 10.6987 31.2331 9.76295 30.7867 8.69268Z" fill="#F1F1F1" />
            <path d="M38.25 0C39.1706 0 40.0428 0.20735 40.8225 0.577895C39.2524 1.86156 38.2501 3.81372 38.2501 5.99997C38.2501 8.18624 39.2524 10.1384 40.8225 11.4221C40.0429 11.7926 39.1707 12 38.25 12C34.9363 12 32.25 9.31371 32.25 6C32.25 2.68629 34.9363 0 38.25 0Z" fill="#3A8138" />
            <path d="M38.5395 4.0015C38.5135 4.0005 38.4874 4 38.4612 4C37.3567 4 36.4612 4.89543 36.4612 6C36.4612 7.10457 37.3567 8 38.4612 8C38.4875 8 38.5136 7.9995 38.5395 7.9985C38.3512 7.36522 38.2501 6.6944 38.2501 5.99997C38.2501 5.30556 38.3512 4.63476 38.5395 4.0015Z" fill="#F1F1F1" />
            <path d="M38.7867 8.69268C38.6362 8.6755 38.4832 8.66667 38.3281 8.66667C36.7632 8.66667 35.4082 9.5653 34.751 10.8747C35.7359 11.5829 36.9442 12 38.25 12C39.1706 12 40.0428 11.7926 40.8225 11.4221C39.9376 10.6987 39.2331 9.76295 38.7867 8.69268Z" fill="#F1F1F1" />
            <path d="M46.25 0C47.1706 0 48.0428 0.20735 48.8225 0.577895C47.2524 1.86156 46.2501 3.81372 46.2501 5.99997C46.2501 8.18624 47.2524 10.1384 48.8225 11.4221C48.0429 11.7926 47.1707 12 46.25 12C42.9363 12 40.25 9.31371 40.25 6C40.25 2.68629 42.9363 0 46.25 0Z" fill="#3A8138" />
            <path d="M46.5395 4.0015C46.5135 4.0005 46.4874 4 46.4612 4C45.3567 4 44.4612 4.89543 44.4612 6C44.4612 7.10457 45.3567 8 46.4612 8C46.4875 8 46.5136 7.9995 46.5395 7.9985C46.3512 7.36522 46.2501 6.6944 46.2501 5.99997C46.2501 5.30556 46.3512 4.63476 46.5395 4.0015Z" fill="#F1F1F1" />
            <path d="M46.7867 8.69268C46.6362 8.6755 46.4832 8.66667 46.3281 8.66667C44.7632 8.66667 43.4082 9.5653 42.751 10.8747C43.7359 11.5829 44.9442 12 46.25 12C47.1706 12 48.0428 11.7926 48.8225 11.4221C47.9376 10.6987 47.2331 9.76295 46.7867 8.69268Z" fill="#F1F1F1" />
            <path d="M54.25 0C55.1706 0 56.0428 0.20735 56.8225 0.577895C55.2524 1.86156 54.2501 3.81372 54.2501 5.99997C54.2501 8.18624 55.2524 10.1384 56.8225 11.4221C56.0429 11.7926 55.1707 12 54.25 12C50.9363 12 48.25 9.31371 48.25 6C48.25 2.68629 50.9363 0 54.25 0Z" fill="#3A8138" />
            <path d="M54.5395 4.0015C54.5135 4.0005 54.4874 4 54.4612 4C53.3567 4 52.4612 4.89543 52.4612 6C52.4612 7.10457 53.3567 8 54.4612 8C54.4875 8 54.5136 7.9995 54.5395 7.9985C54.3512 7.36522 54.2501 6.6944 54.2501 5.99997C54.2501 5.30556 54.3512 4.63476 54.5395 4.0015Z" fill="#F1F1F1" />
            <path d="M54.7867 8.69268C54.6362 8.6755 54.4832 8.66667 54.3281 8.66667C52.7632 8.66667 51.4082 9.5653 50.751 10.8747C51.7359 11.5829 52.9442 12 54.25 12C55.1706 12 56.0428 11.7926 56.8225 11.4221C55.9376 10.6987 55.2331 9.76295 54.7867 8.69268Z" fill="#F1F1F1" />
            <circle cx="62" cy="6" r="6" fill="#3A8138" />
            <path d="M58.501 10.8747C59.1582 9.5653 60.5132 8.66667 62.0781 8.66667C63.6095 8.66667 64.94 9.5273 65.6121 10.7913C64.6072 11.5501 63.3562 12 62 12C60.6942 12 59.4859 11.5829 58.501 10.8747Z" fill="#f1f1f1" />
            <path d="M64.2112 6C64.2112 7.10457 63.3158 8 62.2112 8C61.1067 8 60.2112 7.10457 60.2112 6C60.2112 4.89543 61.1067 4 62.2112 4C63.3158 4 64.2112 4.89543 64.2112 6Z" fill="#f1f1f1" />
      </symbol>
      <symbol id="symbol-member-1" width="12" height="12" viewBox="0 0 12 12">
            <path d="M6.00011 0L11.1963 3V9L6.00011 12L0.803955 9V3L6.00011 0Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-member-10" width="61" height="20" viewBox="0 0 61 20">
            <path d="M6.19243 2.42269L8.19245 1.26798L5.99625 0L0.800098 3V9L0.924059 9.07157L6.00005 6.14095L6.19243 6.25202V2.42269Z" fill="#3A8138" />
            <path d="M15.1924 1.26797L13.1924 2.42268V6.25201L13 6.14095L9.50005 8.16167L7.8001 7.18019V3L12.9961 0L15.1924 1.26797Z" fill="#3A8138" />
            <path d="M19.8062 11.8903V10.0705L14.8001 7.1802V3L19.9963 0L22.1924 1.26798L20.1924 2.42269V9.57739L22.1924 10.7321L19.9963 12L19.8062 11.8903Z" fill="#3A8138" />
            <path d="M29.1924 1.26798L27.1924 2.42269V9.57739L29.1923 10.7321L26.9962 12L21.8 9V3L26.9962 0L29.1924 1.26798Z" fill="#3A8138" />
            <path d="M36.1924 1.26798L34.1924 2.42269V9.57739L36.1923 10.7321L33.9962 12L28.8 9V3L33.9962 0L36.1924 1.26798Z" fill="#3A8138" />
            <path d="M43.1924 1.26798L41.1924 2.42269V9.57739L43.1923 10.7321L40.9962 12L35.8 9V3L40.9962 0L43.1924 1.26798Z" fill="#3A8138" />
            <path d="M50.1924 1.26798L48.1924 2.42269V9.57739L50.1923 10.7321L47.9962 12L42.8 9V3L47.9962 0L50.1924 1.26798Z" fill="#3A8138" />
            <path d="M55 0L60.1962 3V9L55 12L49.8039 9V3L55 0Z" fill="#3A8138" />
            <path d="M8.1924 9.26798L6.19238 10.4227V17.5774L8.19234 18.7321L5.9962 20L0.800049 17V11L5.9962 8L8.1924 9.26798Z" fill="#3A8138" />
            <path d="M13 8L18.1962 11V17L13 20L7.8039 17V11L13 8Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-member-11" width="61" height="20" viewBox="0 0 61 20">
            <path d="M6.19243 2.42269L8.19245 1.26798L5.99625 0L0.800098 3V9L0.924059 9.07157L6.00005 6.14095L6.19243 6.25202V2.42269Z" fill="#3A8138" />
            <path d="M15.1924 1.26797L13.1924 2.42268V6.25201L13 6.14095L9.50005 8.16167L7.8001 7.18019V3L12.9961 0L15.1924 1.26797Z" fill="#3A8138" />
            <path d="M22.1924 1.26797L20.1924 2.42268V6.25201L20 6.14095L16.5 8.16167L14.8001 7.18019V3L19.9961 0L22.1924 1.26797Z" fill="#3A8138" />
            <path d="M26.8062 11.8903V10.0705L21.8001 7.1802V3L26.9963 0L29.1924 1.26798L27.1924 2.42269V9.57739L29.1924 10.7321L26.9963 12L26.8062 11.8903Z" fill="#3A8138" />
            <path d="M36.1924 1.26798L34.1924 2.42269V9.57739L36.1923 10.7321L33.9962 12L28.8 9V3L33.9962 0L36.1924 1.26798Z" fill="#3A8138" />
            <path d="M43.1924 1.26798L41.1924 2.42269V9.57739L43.1923 10.7321L40.9962 12L35.8 9V3L40.9962 0L43.1924 1.26798Z" fill="#3A8138" />
            <path d="M50.1924 1.26798L48.1924 2.42269V9.57739L50.1923 10.7321L47.9962 12L42.8 9V3L47.9962 0L50.1924 1.26798Z" fill="#3A8138" />
            <path d="M55 0L60.1962 3V9L55 12L49.8039 9V3L55 0Z" fill="#3A8138" />
            <path d="M8.1924 9.26798L6.19238 10.4227V17.5774L8.19234 18.7321L5.9962 20L0.800049 17V11L5.9962 8L8.1924 9.26798Z" fill="#3A8138" />
            <path d="M15.1924 9.26798L13.1924 10.4227V17.5774L15.1923 18.7321L12.9962 20L7.80005 17V11L12.9962 8L15.1924 9.26798Z" fill="#3A8138" />
            <path d="M20 8L25.1962 11V17L20 20L14.8039 17V11L20 8Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-member-12" width="61" height="20" viewBox="0 0 61 20">
            <path d="M6.19243 2.42269L8.19245 1.26798L5.99625 0L0.800098 3V9L0.924059 9.07157L6.00005 6.14095L6.19243 6.25202V2.42269Z" fill="#3A8138" />
            <path d="M15.1924 1.26797L13.1924 2.42268V6.25201L13 6.14095L9.50005 8.16167L7.8001 7.18019V3L12.9961 0L15.1924 1.26797Z" fill="#3A8138" />
            <path d="M22.1924 1.26797L20.1924 2.42268V6.25201L20 6.14095L16.5 8.16167L14.8001 7.18019V3L19.9961 0L22.1924 1.26797Z" fill="#3A8138" />
            <path d="M29.1924 1.26797L27.1924 2.42268V6.25201L27 6.14095L23.5 8.16167L21.8001 7.18019V3L26.9961 0L29.1924 1.26797Z" fill="#3A8138" />
            <path d="M33.8062 11.8903V10.0705L28.8001 7.1802V3L33.9963 0L36.1924 1.26798L34.1924 2.42269V9.57739L36.1924 10.7321L33.9963 12L33.8062 11.8903Z" fill="#3A8138" />
            <path d="M43.1924 1.26798L41.1924 2.42269V9.57739L43.1923 10.7321L40.9962 12L35.8 9V3L40.9962 0L43.1924 1.26798Z" fill="#3A8138" />
            <path d="M50.1924 1.26798L48.1924 2.42269V9.57739L50.1923 10.7321L47.9962 12L42.8 9V3L47.9962 0L50.1924 1.26798Z" fill="#3A8138" />
            <path d="M55 0L60.1962 3V9L55 12L49.8039 9V3L55 0Z" fill="#3A8138" />
            <path d="M8.1924 9.26798L6.19238 10.4227V17.5774L8.19234 18.7321L5.9962 20L0.800049 17V11L5.9962 8L8.1924 9.26798Z" fill="#3A8138" />
            <path d="M15.1924 9.26798L13.1924 10.4227V17.5774L15.1923 18.7321L12.9962 20L7.80005 17V11L12.9962 8L15.1924 9.26798Z" fill="#3A8138" />
            <path d="M22.1924 9.26798L20.1924 10.4227V17.5774L22.1923 18.7321L19.9962 20L14.8 17V11L19.9962 8L22.1924 9.26798Z" fill="#3A8138" />
            <path d="M27 8L32.1962 11V17L27 20L21.8039 17V11L27 8Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-member-13" width="61" height="20" viewBox="0 0 61 20">
            <path d="M6.19243 2.42269L8.19245 1.26798L5.99625 0L0.800098 3V9L0.924059 9.07157L6.00005 6.14095L6.19243 6.25202V2.42269Z" fill="#3A8138" />
            <path d="M15.1924 1.26797L13.1924 2.42268V6.25201L13 6.14095L9.50005 8.16167L7.8001 7.18019V3L12.9961 0L15.1924 1.26797Z" fill="#3A8138" />
            <path d="M22.1924 1.26797L20.1924 2.42268V6.25201L20 6.14095L16.5 8.16167L14.8001 7.18019V3L19.9961 0L22.1924 1.26797Z" fill="#3A8138" />
            <path d="M29.1924 1.26797L27.1924 2.42268V6.25201L27 6.14095L23.5 8.16167L21.8001 7.18019V3L26.9961 0L29.1924 1.26797Z" fill="#3A8138" />
            <path d="M36.1924 1.26797L34.1924 2.42268V6.25201L34 6.14095L30.5 8.16167L28.8001 7.18019V3L33.9961 0L36.1924 1.26797Z" fill="#3A8138" />
            <path d="M40.8062 11.8903V10.0705L35.8001 7.1802V3L40.9963 0L43.1924 1.26798L41.1924 2.42269V9.57739L43.1924 10.7321L40.9963 12L40.8062 11.8903Z" fill="#3A8138" />
            <path d="M50.1924 1.26798L48.1924 2.42269V9.57739L50.1923 10.7321L47.9962 12L42.8 9V3L47.9962 0L50.1924 1.26798Z" fill="#3A8138" />
            <path d="M55 0L60.1962 3V9L55 12L49.8039 9V3L55 0Z" fill="#3A8138" />
            <path d="M8.1924 9.26798L6.19238 10.4227V17.5774L8.19234 18.7321L5.9962 20L0.800049 17V11L5.9962 8L8.1924 9.26798Z" fill="#3A8138" />
            <path d="M15.1924 9.26798L13.1924 10.4227V17.5774L15.1923 18.7321L12.9962 20L7.80005 17V11L12.9962 8L15.1924 9.26798Z" fill="#3A8138" />
            <path d="M22.1924 9.26798L20.1924 10.4227V17.5774L22.1923 18.7321L19.9962 20L14.8 17V11L19.9962 8L22.1924 9.26798Z" fill="#3A8138" />
            <path d="M29.1924 9.26798L27.1924 10.4227V17.5774L29.1923 18.7321L26.9962 20L21.8 17V11L26.9962 8L29.1924 9.26798Z" fill="#3A8138" />
            <path d="M34 8L39.1962 11V17L34 20L28.8039 17V11L34 8Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-member-14" width="61" height="20" viewBox="0 0 61 20">
            <path d="M6.19243 2.42269L8.19245 1.26798L5.99625 0L0.800098 3V9L0.924059 9.07157L6.00005 6.14095L6.19243 6.25202V2.42269Z" fill="#3A8138" />
            <path d="M15.1924 1.26797L13.1924 2.42268V6.25201L13 6.14095L9.50005 8.16167L7.8001 7.18019V3L12.9961 0L15.1924 1.26797Z" fill="#3A8138" />
            <path d="M22.1924 1.26797L20.1924 2.42268V6.25201L20 6.14095L16.5 8.16167L14.8001 7.18019V3L19.9961 0L22.1924 1.26797Z" fill="#3A8138" />
            <path d="M29.1924 1.26797L27.1924 2.42268V6.25201L27 6.14095L23.5 8.16167L21.8001 7.18019V3L26.9961 0L29.1924 1.26797Z" fill="#3A8138" />
            <path d="M36.1924 1.26797L34.1924 2.42268V6.25201L34 6.14095L30.5 8.16167L28.8001 7.18019V3L33.9961 0L36.1924 1.26797Z" fill="#3A8138" />
            <path d="M43.1924 1.26797L41.1924 2.42268V6.25201L41 6.14095L37.5 8.16167L35.8001 7.18019V3L40.9961 0L43.1924 1.26797Z" fill="#3A8138" />
            <path d="M47.8062 11.8903V10.0705L42.8001 7.1802V3L47.9963 0L50.1924 1.26798L48.1924 2.42269V9.57739L50.1924 10.7321L47.9963 12L47.8062 11.8903Z" fill="#3A8138" />
            <path d="M55 0L60.1962 3V9L55 12L49.8039 9V3L55 0Z" fill="#3A8138" />
            <path d="M8.1924 9.26798L6.19238 10.4227V17.5774L8.19234 18.7321L5.9962 20L0.800049 17V11L5.9962 8L8.1924 9.26798Z" fill="#3A8138" />
            <path d="M15.1924 9.26798L13.1924 10.4227V17.5774L15.1923 18.7321L12.9962 20L7.80005 17V11L12.9962 8L15.1924 9.26798Z" fill="#3A8138" />
            <path d="M22.1924 9.26798L20.1924 10.4227V17.5774L22.1923 18.7321L19.9962 20L14.8 17V11L19.9962 8L22.1924 9.26798Z" fill="#3A8138" />
            <path d="M29.1924 9.26798L27.1924 10.4227V17.5774L29.1923 18.7321L26.9962 20L21.8 17V11L26.9962 8L29.1924 9.26798Z" fill="#3A8138" />
            <path d="M36.1924 9.26798L34.1924 10.4227V17.5774L36.1923 18.7321L33.9962 20L28.8 17V11L33.9962 8L36.1924 9.26798Z" fill="#3A8138" />
            <path d="M41 8L46.1962 11V17L41 20L35.8039 17V11L41 8Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-member-15" width="61" height="20" viewBox="0 0 61 20">
            <path d="M6.19243 2.42269L8.19245 1.26798L5.99625 0L0.800098 3V9L0.924059 9.07157L6.00005 6.14095L6.19243 6.25202V2.42269Z" fill="#3A8138" />
            <path d="M15.1924 1.26797L13.1924 2.42268V6.25201L13 6.14095L9.50005 8.16167L7.8001 7.18019V3L12.9961 0L15.1924 1.26797Z" fill="#3A8138" />
            <path d="M22.1924 1.26797L20.1924 2.42268V6.25201L20 6.14095L16.5 8.16167L14.8001 7.18019V3L19.9961 0L22.1924 1.26797Z" fill="#3A8138" />
            <path d="M29.1924 1.26797L27.1924 2.42268V6.25201L27 6.14095L23.5 8.16167L21.8001 7.18019V3L26.9961 0L29.1924 1.26797Z" fill="#3A8138" />
            <path d="M36.1924 1.26797L34.1924 2.42268V6.25201L34 6.14095L30.5 8.16167L28.8001 7.18019V3L33.9961 0L36.1924 1.26797Z" fill="#3A8138" />
            <path d="M43.1924 1.26797L41.1924 2.42268V6.25201L41 6.14095L37.5 8.16167L35.8001 7.18019V3L40.9961 0L43.1924 1.26797Z" fill="#3A8138" />
            <path d="M50.1924 1.26797L48.1924 2.42268V6.25201L48 6.14095L44.5 8.16167L42.8001 7.18019V3L47.9961 0L50.1924 1.26797Z" fill="#3A8138" />
            <path d="M60.1963 3L55.0002 0L49.804 3V7.18244L54.8062 10.0705V11.888L55.0002 12L60.1963 9V3Z" fill="#3A8138" />
            <path d="M8.1924 9.26798L6.19238 10.4227V17.5774L8.19234 18.7321L5.9962 20L0.800049 17V11L5.9962 8L8.1924 9.26798Z" fill="#3A8138" />
            <path d="M15.1924 9.26798L13.1924 10.4227V17.5774L15.1923 18.7321L12.9962 20L7.80005 17V11L12.9962 8L15.1924 9.26798Z" fill="#3A8138" />
            <path d="M22.1924 9.26798L20.1924 10.4227V17.5774L22.1923 18.7321L19.9962 20L14.8 17V11L19.9962 8L22.1924 9.26798Z" fill="#3A8138" />
            <path d="M29.1924 9.26798L27.1924 10.4227V17.5774L29.1923 18.7321L26.9962 20L21.8 17V11L26.9962 8L29.1924 9.26798Z" fill="#3A8138" />
            <path d="M36.1924 9.26798L34.1924 10.4227V17.5774L36.1923 18.7321L33.9962 20L28.8 17V11L33.9962 8L36.1924 9.26798Z" fill="#3A8138" />
            <path d="M43.1924 9.26798L41.1924 10.4227V17.5774L43.1923 18.7321L40.9962 20L35.8 17V11L40.9962 8L43.1924 9.26798Z" fill="#3A8138" />
            <path d="M48 8L53.1962 11V17L48 20L42.8039 17V11L48 8Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-member-16" width="61" height="20" viewBox="0 0 61 20">
            <path d="M6.19243 2.42269L8.19245 1.26798L5.99625 0L0.800098 3V9L0.924059 9.07157L6.00005 6.14095L6.19243 6.25202V2.42269Z" fill="#3A8138" />
            <path d="M15.1924 1.26797L13.1924 2.42268V6.25201L13 6.14095L9.50005 8.16167L7.8001 7.18019V3L12.9961 0L15.1924 1.26797Z" fill="#3A8138" />
            <path d="M22.1924 1.26797L20.1924 2.42268V6.25201L20 6.14095L16.5 8.16167L14.8001 7.18019V3L19.9961 0L22.1924 1.26797Z" fill="#3A8138" />
            <path d="M29.1924 1.26797L27.1924 2.42268V6.25201L27 6.14095L23.5 8.16167L21.8001 7.18019V3L26.9961 0L29.1924 1.26797Z" fill="#3A8138" />
            <path d="M36.1924 1.26797L34.1924 2.42268V6.25201L34 6.14095L30.5 8.16167L28.8001 7.18019V3L33.9961 0L36.1924 1.26797Z" fill="#3A8138" />
            <path d="M43.1924 1.26797L41.1924 2.42268V6.25201L41 6.14095L37.5 8.16167L35.8001 7.18019V3L40.9961 0L43.1924 1.26797Z" fill="#3A8138" />
            <path d="M50.1924 1.26797L48.1924 2.42268V6.25201L48 6.14095L44.5 8.16167L42.8001 7.18019V3L47.9961 0L50.1924 1.26797Z" fill="#3A8138" />
            <path d="M55.0002 0L60.1963 3V9L60.0743 9.07046L55.0002 6.14093L51.5001 8.16169L49.804 7.18244V3L55.0002 0Z" fill="#3A8138" />
            <path d="M8.1924 9.26798L6.19238 10.4227V17.5774L8.19234 18.7321L5.9962 20L0.800049 17V11L5.9962 8L8.1924 9.26798Z" fill="#3A8138" />
            <path d="M15.1924 9.26798L13.1924 10.4227V17.5774L15.1923 18.7321L12.9962 20L7.80005 17V11L12.9962 8L15.1924 9.26798Z" fill="#3A8138" />
            <path d="M22.1924 9.26798L20.1924 10.4227V17.5774L22.1923 18.7321L19.9962 20L14.8 17V11L19.9962 8L22.1924 9.26798Z" fill="#3A8138" />
            <path d="M29.1924 9.26798L27.1924 10.4227V17.5774L29.1923 18.7321L26.9962 20L21.8 17V11L26.9962 8L29.1924 9.26798Z" fill="#3A8138" />
            <path d="M36.1924 9.26798L34.1924 10.4227V17.5774L36.1923 18.7321L33.9962 20L28.8 17V11L33.9962 8L36.1924 9.26798Z" fill="#3A8138" />
            <path d="M43.1924 9.26798L41.1924 10.4227V17.5774L43.1923 18.7321L40.9962 20L35.8 17V11L40.9962 8L43.1924 9.26798Z" fill="#3A8138" />
            <path d="M50.1924 9.26798L48.1924 10.4227V17.5774L50.1923 18.7321L47.9962 20L42.8 17V11L47.9962 8L50.1924 9.26798Z" fill="#3A8138" />
            <path d="M55 8L60.1962 11V17L55 20L49.8039 17V11L55 8Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-member-17" width="61" height="28" viewBox="0 0 61 28">
            <path d="M8.19238 1.26797L6.19238 2.42268V6.25201L6 6.14095L2.5 8.16167L0.800049 7.18019V3L5.99609 0L8.19238 1.26797Z" fill="#3A8138" />
            <path d="M15.1924 1.26797L13.1924 2.42268V6.25201L13 6.14095L9.5 8.16167L7.80005 7.18019V3L12.9961 0L15.1924 1.26797Z" fill="#3A8138" />
            <path d="M22.1924 1.26797L20.1924 2.42268V6.25201L20 6.14095L16.5 8.16167L14.8 7.18019V3L19.9961 0L22.1924 1.26797Z" fill="#3A8138" />
            <path d="M29.1924 1.26797L27.1924 2.42268V6.25201L27 6.14095L23.5 8.16167L21.8 7.18019V3L26.9961 0L29.1924 1.26797Z" fill="#3A8138" />
            <path d="M36.1924 1.26797L34.1924 2.42268V6.25201L34 6.14095L30.5 8.16167L28.8 7.18019V3L33.9961 0L36.1924 1.26797Z" fill="#3A8138" />
            <path d="M43.1924 1.26797L41.1924 2.42268V6.25201L41 6.14095L37.5 8.16167L35.8 7.18019V3L40.9961 0L43.1924 1.26797Z" fill="#3A8138" />
            <path d="M50.1924 1.26797L48.1924 2.42268V6.25201L48 6.14095L44.5 8.16167L42.8 7.18019V3L47.9961 0L50.1924 1.26797Z" fill="#3A8138" />
            <path d="M55.0001 0L60.1963 3V9L60.0742 9.07046L55.0001 6.14093L51.5001 8.16169L49.804 7.18244V3L55.0001 0Z" fill="#3A8138" />
            <path d="M6.19238 10.4227L8.1924 9.26798L5.9962 8L0.800049 11V17L0.924011 17.0716L6 14.1409L6.19238 14.252V10.4227Z" fill="#3A8138" />
            <path d="M12.8062 19.8903V18.0705L7.80005 15.1802V11L12.9962 8L15.1924 9.26798L13.1924 10.4227V17.5774L15.1923 18.7321L12.9962 20L12.8062 19.8903Z" fill="#3A8138" />
            <path d="M22.1924 9.26798L20.1923 10.4227V17.5774L22.1923 18.7321L19.9962 20L14.8 17V11L19.9962 8L22.1924 9.26798Z" fill="#3A8138" />
            <path d="M29.1924 9.26798L27.1923 10.4227V17.5774L29.1923 18.7321L26.9962 20L21.8 17V11L26.9962 8L29.1924 9.26798Z" fill="#3A8138" />
            <path d="M36.1924 9.26798L34.1923 10.4227V17.5774L36.1923 18.7321L33.9962 20L28.8 17V11L33.9962 8L36.1924 9.26798Z" fill="#3A8138" />
            <path d="M43.1924 9.26798L41.1923 10.4227V17.5774L43.1923 18.7321L40.9962 20L35.8 17V11L40.9962 8L43.1924 9.26798Z" fill="#3A8138" />
            <path d="M50.1924 9.26798L48.1923 10.4227V17.5774L50.1923 18.7321L47.9962 20L42.8 17V11L47.9962 8L50.1924 9.26798Z" fill="#3A8138" />
            <path d="M55 8L60.1962 11V17L55 20L49.8038 17V11L55 8Z" fill="#3A8138" />
            <path d="M6 16L11.1962 19V25L6 28L0.803848 25V19L6 16Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-member-18" width="61" height="28" viewBox="0 0 61 28">
            <path d="M8.19243 1.26797L6.19243 2.42268V6.25201L6.00005 6.14095L2.50005 8.16167L0.800098 7.18019V3L5.99614 0L8.19243 1.26797Z" fill="#3A8138" />
            <path d="M15.1924 1.26797L13.1924 2.42268V6.25201L13 6.14095L9.50005 8.16167L7.8001 7.18019V3L12.9961 0L15.1924 1.26797Z" fill="#3A8138" />
            <path d="M22.1924 1.26797L20.1924 2.42268V6.25201L20 6.14095L16.5 8.16167L14.8001 7.18019V3L19.9961 0L22.1924 1.26797Z" fill="#3A8138" />
            <path d="M29.1924 1.26797L27.1924 2.42268V6.25201L27 6.14095L23.5 8.16167L21.8001 7.18019V3L26.9961 0L29.1924 1.26797Z" fill="#3A8138" />
            <path d="M36.1924 1.26797L34.1924 2.42268V6.25201L34 6.14095L30.5 8.16167L28.8001 7.18019V3L33.9961 0L36.1924 1.26797Z" fill="#3A8138" />
            <path d="M43.1924 1.26797L41.1924 2.42268V6.25201L41 6.14095L37.5 8.16167L35.8001 7.18019V3L40.9961 0L43.1924 1.26797Z" fill="#3A8138" />
            <path d="M50.1924 1.26797L48.1924 2.42268V6.25201L48 6.14095L44.5 8.16167L42.8001 7.18019V3L47.9961 0L50.1924 1.26797Z" fill="#3A8138" />
            <path d="M55.0002 0L60.1963 3V9L60.0743 9.07046L55.0002 6.14093L51.5001 8.16169L49.804 7.18244V3L55.0002 0Z" fill="#3A8138" />
            <path d="M6.19243 10.4227L8.19245 9.26798L5.99625 8L0.800098 11V17L0.924059 17.0716L6.00005 14.1409L6.19243 14.252V10.4227Z" fill="#3A8138" />
            <path d="M15.1924 9.26797L13.1924 10.4227V14.252L13 14.1409L9.50005 16.1617L7.8001 15.1802V11L12.9961 8L15.1924 9.26797Z" fill="#3A8138" />
            <path d="M19.8062 19.8903V18.0705L14.8001 15.1802V11L19.9963 8L22.1924 9.26798L20.1924 10.4227V17.5774L22.1924 18.7321L19.9963 20L19.8062 19.8903Z" fill="#3A8138" />
            <path d="M29.1924 9.26798L27.1924 10.4227V17.5774L29.1923 18.7321L26.9962 20L21.8 17V11L26.9962 8L29.1924 9.26798Z" fill="#3A8138" />
            <path d="M36.1924 9.26798L34.1924 10.4227V17.5774L36.1923 18.7321L33.9962 20L28.8 17V11L33.9962 8L36.1924 9.26798Z" fill="#3A8138" />
            <path d="M43.1924 9.26798L41.1924 10.4227V17.5774L43.1923 18.7321L40.9962 20L35.8 17V11L40.9962 8L43.1924 9.26798Z" fill="#3A8138" />
            <path d="M50.1924 9.26798L48.1924 10.4227V17.5774L50.1923 18.7321L47.9962 20L42.8 17V11L47.9962 8L50.1924 9.26798Z" fill="#3A8138" />
            <path d="M55 8L60.1962 11V17L55 20L49.8039 17V11L55 8Z" fill="#3A8138" />
            <path d="M8.1924 17.268L6.19238 18.4227V25.5774L8.19234 26.7321L5.9962 28L0.800049 25V19L5.9962 16L8.1924 17.268Z" fill="#3A8138" />
            <path d="M13 16L18.1962 19V25L13 28L7.8039 25V19L13 16Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-member-19" width="61" height="28" viewBox="0 0 61 28">
            <path d="M8.19243 1.26797L6.19243 2.42268V6.25201L6.00005 6.14095L2.50005 8.16167L0.800098 7.18019V3L5.99614 0L8.19243 1.26797Z" fill="#3A8138" />
            <path d="M15.1924 1.26797L13.1924 2.42268V6.25201L13 6.14095L9.50005 8.16167L7.8001 7.18019V3L12.9961 0L15.1924 1.26797Z" fill="#3A8138" />
            <path d="M22.1924 1.26797L20.1924 2.42268V6.25201L20 6.14095L16.5 8.16167L14.8001 7.18019V3L19.9961 0L22.1924 1.26797Z" fill="#3A8138" />
            <path d="M29.1924 1.26797L27.1924 2.42268V6.25201L27 6.14095L23.5 8.16167L21.8001 7.18019V3L26.9961 0L29.1924 1.26797Z" fill="#3A8138" />
            <path d="M36.1924 1.26797L34.1924 2.42268V6.25201L34 6.14095L30.5 8.16167L28.8001 7.18019V3L33.9961 0L36.1924 1.26797Z" fill="#3A8138" />
            <path d="M43.1924 1.26797L41.1924 2.42268V6.25201L41 6.14095L37.5 8.16167L35.8001 7.18019V3L40.9961 0L43.1924 1.26797Z" fill="#3A8138" />
            <path d="M50.1924 1.26797L48.1924 2.42268V6.25201L48 6.14095L44.5 8.16167L42.8001 7.18019V3L47.9961 0L50.1924 1.26797Z" fill="#3A8138" />
            <path d="M55.0002 0L60.1963 3V9L60.0743 9.07046L55.0002 6.14093L51.5001 8.16169L49.804 7.18244V3L55.0002 0Z" fill="#3A8138" />
            <path d="M6.19243 10.4227L8.19245 9.26798L5.99625 8L0.800098 11V17L0.924059 17.0716L6.00005 14.1409L6.19243 14.252V10.4227Z" fill="#3A8138" />
            <path d="M15.1924 9.26797L13.1924 10.4227V14.252L13 14.1409L9.50005 16.1617L7.8001 15.1802V11L12.9961 8L15.1924 9.26797Z" fill="#3A8138" />
            <path d="M22.1924 9.26797L20.1924 10.4227V14.252L20 14.1409L16.5 16.1617L14.8001 15.1802V11L19.9961 8L22.1924 9.26797Z" fill="#3A8138" />
            <path d="M26.8062 19.8903V18.0705L21.8001 15.1802V11L26.9963 8L29.1924 9.26798L27.1924 10.4227V17.5774L29.1924 18.7321L26.9963 20L26.8062 19.8903Z" fill="#3A8138" />
            <path d="M36.1924 9.26798L34.1924 10.4227V17.5774L36.1923 18.7321L33.9962 20L28.8 17V11L33.9962 8L36.1924 9.26798Z" fill="#3A8138" />
            <path d="M43.1924 9.26798L41.1924 10.4227V17.5774L43.1923 18.7321L40.9962 20L35.8 17V11L40.9962 8L43.1924 9.26798Z" fill="#3A8138" />
            <path d="M50.1924 9.26798L48.1924 10.4227V17.5774L50.1923 18.7321L47.9962 20L42.8 17V11L47.9962 8L50.1924 9.26798Z" fill="#3A8138" />
            <path d="M55 8L60.1962 11V17L55 20L49.8039 17V11L55 8Z" fill="#3A8138" />
            <path d="M8.1924 17.268L6.19238 18.4227V25.5774L8.19234 26.7321L5.9962 28L0.800049 25V19L5.9962 16L8.1924 17.268Z" fill="#3A8138" />
            <path d="M15.1924 17.268L13.1924 18.4227V25.5774L15.1923 26.7321L12.9962 28L7.80005 25V19L12.9962 16L15.1924 17.268Z" fill="#3A8138" />
            <path d="M20 16L25.1962 19V25L20 28L14.8039 25V19L20 16Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-member-2" width="18" height="12" viewBox="0 0 18 12">
            <path d="M7.39235 1.26798L5.39233 2.42269V9.57739L7.39229 10.7321L5.19615 12L0 9V3L5.19615 0L7.39235 1.26798Z" fill="#3A8138" />
            <path d="M12.2 0L17.3962 3V9L12.2 12L7.00385 9V3L12.2 0Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-member-20" width="61" height="28" viewBox="0 0 61 28">
            <path d="M8.19243 1.26797L6.19243 2.42268V6.25201L6.00005 6.14095L2.50005 8.16167L0.800098 7.18019V3L5.99614 0L8.19243 1.26797Z" fill="#3A8138" />
            <path d="M15.1924 1.26797L13.1924 2.42268V6.25201L13 6.14095L9.50005 8.16167L7.8001 7.18019V3L12.9961 0L15.1924 1.26797Z" fill="#3A8138" />
            <path d="M22.1924 1.26797L20.1924 2.42268V6.25201L20 6.14095L16.5 8.16167L14.8001 7.18019V3L19.9961 0L22.1924 1.26797Z" fill="#3A8138" />
            <path d="M29.1924 1.26797L27.1924 2.42268V6.25201L27 6.14095L23.5 8.16167L21.8001 7.18019V3L26.9961 0L29.1924 1.26797Z" fill="#3A8138" />
            <path d="M36.1924 1.26797L34.1924 2.42268V6.25201L34 6.14095L30.5 8.16167L28.8001 7.18019V3L33.9961 0L36.1924 1.26797Z" fill="#3A8138" />
            <path d="M43.1924 1.26797L41.1924 2.42268V6.25201L41 6.14095L37.5 8.16167L35.8001 7.18019V3L40.9961 0L43.1924 1.26797Z" fill="#3A8138" />
            <path d="M50.1924 1.26797L48.1924 2.42268V6.25201L48 6.14095L44.5 8.16167L42.8001 7.18019V3L47.9961 0L50.1924 1.26797Z" fill="#3A8138" />
            <path d="M55.0002 0L60.1963 3V9L60.0743 9.07046L55.0002 6.14093L51.5001 8.16169L49.804 7.18244V3L55.0002 0Z" fill="#3A8138" />
            <path d="M6.19243 10.4227L8.19245 9.26798L5.99625 8L0.800098 11V17L0.924059 17.0716L6.00005 14.1409L6.19243 14.252V10.4227Z" fill="#3A8138" />
            <path d="M15.1924 9.26797L13.1924 10.4227V14.252L13 14.1409L9.50005 16.1617L7.8001 15.1802V11L12.9961 8L15.1924 9.26797Z" fill="#3A8138" />
            <path d="M22.1924 9.26797L20.1924 10.4227V14.252L20 14.1409L16.5 16.1617L14.8001 15.1802V11L19.9961 8L22.1924 9.26797Z" fill="#3A8138" />
            <path d="M29.1924 9.26797L27.1924 10.4227V14.252L27 14.1409L23.5 16.1617L21.8001 15.1802V11L26.9961 8L29.1924 9.26797Z" fill="#3A8138" />
            <path d="M33.8062 19.8903V18.0705L28.8001 15.1802V11L33.9963 8L36.1924 9.26798L34.1924 10.4227V17.5774L36.1924 18.7321L33.9963 20L33.8062 19.8903Z" fill="#3A8138" />
            <path d="M43.1924 9.26798L41.1924 10.4227V17.5774L43.1923 18.7321L40.9962 20L35.8 17V11L40.9962 8L43.1924 9.26798Z" fill="#3A8138" />
            <path d="M50.1924 9.26798L48.1924 10.4227V17.5774L50.1923 18.7321L47.9962 20L42.8 17V11L47.9962 8L50.1924 9.26798Z" fill="#3A8138" />
            <path d="M55 8L60.1962 11V17L55 20L49.8039 17V11L55 8Z" fill="#3A8138" />
            <path d="M8.1924 17.268L6.19238 18.4227V25.5774L8.19234 26.7321L5.9962 28L0.800049 25V19L5.9962 16L8.1924 17.268Z" fill="#3A8138" />
            <path d="M15.1924 17.268L13.1924 18.4227V25.5774L15.1923 26.7321L12.9962 28L7.80005 25V19L12.9962 16L15.1924 17.268Z" fill="#3A8138" />
            <path d="M22.1924 17.268L20.1924 18.4227V25.5774L22.1923 26.7321L19.9962 28L14.8 25V19L19.9962 16L22.1924 17.268Z" fill="#3A8138" />
            <path d="M27 16L32.1962 19V25L27 28L21.8039 25V19L27 16Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-member-21" width="61" height="28" viewBox="0 0 61 28">
            <path d="M8.19243 1.26797L6.19243 2.42268V6.25201L6.00005 6.14095L2.50005 8.16167L0.800098 7.18019V3L5.99614 0L8.19243 1.26797Z" fill="#3A8138" />
            <path d="M15.1924 1.26797L13.1924 2.42268V6.25201L13 6.14095L9.50005 8.16167L7.8001 7.18019V3L12.9961 0L15.1924 1.26797Z" fill="#3A8138" />
            <path d="M22.1924 1.26797L20.1924 2.42268V6.25201L20 6.14095L16.5 8.16167L14.8001 7.18019V3L19.9961 0L22.1924 1.26797Z" fill="#3A8138" />
            <path d="M29.1924 1.26797L27.1924 2.42268V6.25201L27 6.14095L23.5 8.16167L21.8001 7.18019V3L26.9961 0L29.1924 1.26797Z" fill="#3A8138" />
            <path d="M36.1924 1.26797L34.1924 2.42268V6.25201L34 6.14095L30.5 8.16167L28.8001 7.18019V3L33.9961 0L36.1924 1.26797Z" fill="#3A8138" />
            <path d="M43.1924 1.26797L41.1924 2.42268V6.25201L41 6.14095L37.5 8.16167L35.8001 7.18019V3L40.9961 0L43.1924 1.26797Z" fill="#3A8138" />
            <path d="M50.1924 1.26797L48.1924 2.42268V6.25201L48 6.14095L44.5 8.16167L42.8001 7.18019V3L47.9961 0L50.1924 1.26797Z" fill="#3A8138" />
            <path d="M55.0002 0L60.1963 3V9L60.0743 9.07046L55.0002 6.14093L51.5001 8.16169L49.804 7.18244V3L55.0002 0Z" fill="#3A8138" />
            <path d="M6.19243 10.4227L8.19245 9.26798L5.99625 8L0.800098 11V17L0.924059 17.0716L6.00005 14.1409L6.19243 14.252V10.4227Z" fill="#3A8138" />
            <path d="M15.1924 9.26797L13.1924 10.4227V14.252L13 14.1409L9.50005 16.1617L7.8001 15.1802V11L12.9961 8L15.1924 9.26797Z" fill="#3A8138" />
            <path d="M22.1924 9.26797L20.1924 10.4227V14.252L20 14.1409L16.5 16.1617L14.8001 15.1802V11L19.9961 8L22.1924 9.26797Z" fill="#3A8138" />
            <path d="M29.1924 9.26797L27.1924 10.4227V14.252L27 14.1409L23.5 16.1617L21.8001 15.1802V11L26.9961 8L29.1924 9.26797Z" fill="#3A8138" />
            <path d="M36.1924 9.26797L34.1924 10.4227V14.252L34 14.1409L30.5 16.1617L28.8001 15.1802V11L33.9961 8L36.1924 9.26797Z" fill="#3A8138" />
            <path d="M40.8062 19.8903V18.0705L35.8001 15.1802V11L40.9963 8L43.1924 9.26798L41.1924 10.4227V17.5774L43.1924 18.7321L40.9963 20L40.8062 19.8903Z" fill="#3A8138" />
            <path d="M50.1924 9.26798L48.1924 10.4227V17.5774L50.1923 18.7321L47.9962 20L42.8 17V11L47.9962 8L50.1924 9.26798Z" fill="#3A8138" />
            <path d="M55 8L60.1962 11V17L55 20L49.8039 17V11L55 8Z" fill="#3A8138" />
            <path d="M8.1924 17.268L6.19238 18.4227V25.5774L8.19234 26.7321L5.9962 28L0.800049 25V19L5.9962 16L8.1924 17.268Z" fill="#3A8138" />
            <path d="M15.1924 17.268L13.1924 18.4227V25.5774L15.1923 26.7321L12.9962 28L7.80005 25V19L12.9962 16L15.1924 17.268Z" fill="#3A8138" />
            <path d="M22.1924 17.268L20.1924 18.4227V25.5774L22.1923 26.7321L19.9962 28L14.8 25V19L19.9962 16L22.1924 17.268Z" fill="#3A8138" />
            <path d="M29.1924 17.268L27.1924 18.4227V25.5774L29.1923 26.7321L26.9962 28L21.8 25V19L26.9962 16L29.1924 17.268Z" fill="#3A8138" />
            <path d="M34 16L39.1962 19V25L34 28L28.8039 25V19L34 16Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-member-22" width="61" height="28" viewBox="0 0 61 28">
            <path d="M8.19243 1.26797L6.19243 2.42268V6.25201L6.00005 6.14095L2.50005 8.16167L0.800098 7.18019V3L5.99614 0L8.19243 1.26797Z" fill="#3A8138" />
            <path d="M15.1924 1.26797L13.1924 2.42268V6.25201L13 6.14095L9.50005 8.16167L7.8001 7.18019V3L12.9961 0L15.1924 1.26797Z" fill="#3A8138" />
            <path d="M22.1924 1.26797L20.1924 2.42268V6.25201L20 6.14095L16.5 8.16167L14.8001 7.18019V3L19.9961 0L22.1924 1.26797Z" fill="#3A8138" />
            <path d="M29.1924 1.26797L27.1924 2.42268V6.25201L27 6.14095L23.5 8.16167L21.8001 7.18019V3L26.9961 0L29.1924 1.26797Z" fill="#3A8138" />
            <path d="M36.1924 1.26797L34.1924 2.42268V6.25201L34 6.14095L30.5 8.16167L28.8001 7.18019V3L33.9961 0L36.1924 1.26797Z" fill="#3A8138" />
            <path d="M43.1924 1.26797L41.1924 2.42268V6.25201L41 6.14095L37.5 8.16167L35.8001 7.18019V3L40.9961 0L43.1924 1.26797Z" fill="#3A8138" />
            <path d="M50.1924 1.26797L48.1924 2.42268V6.25201L48 6.14095L44.5 8.16167L42.8001 7.18019V3L47.9961 0L50.1924 1.26797Z" fill="#3A8138" />
            <path d="M55.0002 0L60.1963 3V9L60.0743 9.07046L55.0002 6.14093L51.5001 8.16169L49.804 7.18244V3L55.0002 0Z" fill="#3A8138" />
            <path d="M6.19243 10.4227L8.19245 9.26798L5.99625 8L0.800098 11V17L0.924059 17.0716L6.00005 14.1409L6.19243 14.252V10.4227Z" fill="#3A8138" />
            <path d="M15.1924 9.26797L13.1924 10.4227V14.252L13 14.1409L9.50005 16.1617L7.8001 15.1802V11L12.9961 8L15.1924 9.26797Z" fill="#3A8138" />
            <path d="M22.1924 9.26797L20.1924 10.4227V14.252L20 14.1409L16.5 16.1617L14.8001 15.1802V11L19.9961 8L22.1924 9.26797Z" fill="#3A8138" />
            <path d="M29.1924 9.26797L27.1924 10.4227V14.252L27 14.1409L23.5 16.1617L21.8001 15.1802V11L26.9961 8L29.1924 9.26797Z" fill="#3A8138" />
            <path d="M36.1924 9.26797L34.1924 10.4227V14.252L34 14.1409L30.5 16.1617L28.8001 15.1802V11L33.9961 8L36.1924 9.26797Z" fill="#3A8138" />
            <path d="M43.1924 9.26797L41.1924 10.4227V14.252L41 14.1409L37.5 16.1617L35.8001 15.1802V11L40.9961 8L43.1924 9.26797Z" fill="#3A8138" />
            <path d="M47.8062 19.8903V18.0705L42.8001 15.1802V11L47.9963 8L50.1924 9.26798L48.1924 10.4227V17.5774L50.1924 18.7321L47.9963 20L47.8062 19.8903Z" fill="#3A8138" />
            <path d="M55 8L60.1962 11V17L55 20L49.8039 17V11L55 8Z" fill="#3A8138" />
            <path d="M8.1924 17.268L6.19238 18.4227V25.5774L8.19234 26.7321L5.9962 28L0.800049 25V19L5.9962 16L8.1924 17.268Z" fill="#3A8138" />
            <path d="M15.1924 17.268L13.1924 18.4227V25.5774L15.1923 26.7321L12.9962 28L7.80005 25V19L12.9962 16L15.1924 17.268Z" fill="#3A8138" />
            <path d="M22.1924 17.268L20.1924 18.4227V25.5774L22.1923 26.7321L19.9962 28L14.8 25V19L19.9962 16L22.1924 17.268Z" fill="#3A8138" />
            <path d="M29.1924 17.268L27.1924 18.4227V25.5774L29.1923 26.7321L26.9962 28L21.8 25V19L26.9962 16L29.1924 17.268Z" fill="#3A8138" />
            <path d="M36.1924 17.268L34.1924 18.4227V25.5774L36.1923 26.7321L33.9962 28L28.8 25V19L33.9962 16L36.1924 17.268Z" fill="#3A8138" />
            <path d="M41 16L46.1962 19V25L41 28L35.8039 25V19L41 16Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-member-23" width="61" height="28" viewBox="0 0 61 28">
            <path d="M8.19243 1.26797L6.19243 2.42268V6.25201L6.00005 6.14095L2.50005 8.16167L0.800098 7.18019V3L5.99614 0L8.19243 1.26797Z" fill="#3A8138" />
            <path d="M15.1924 1.26797L13.1924 2.42268V6.25201L13 6.14095L9.50005 8.16167L7.8001 7.18019V3L12.9961 0L15.1924 1.26797Z" fill="#3A8138" />
            <path d="M22.1924 1.26797L20.1924 2.42268V6.25201L20 6.14095L16.5 8.16167L14.8001 7.18019V3L19.9961 0L22.1924 1.26797Z" fill="#3A8138" />
            <path d="M29.1924 1.26797L27.1924 2.42268V6.25201L27 6.14095L23.5 8.16167L21.8001 7.18019V3L26.9961 0L29.1924 1.26797Z" fill="#3A8138" />
            <path d="M36.1924 1.26797L34.1924 2.42268V6.25201L34 6.14095L30.5 8.16167L28.8001 7.18019V3L33.9961 0L36.1924 1.26797Z" fill="#3A8138" />
            <path d="M43.1924 1.26797L41.1924 2.42268V6.25201L41 6.14095L37.5 8.16167L35.8001 7.18019V3L40.9961 0L43.1924 1.26797Z" fill="#3A8138" />
            <path d="M50.1924 1.26797L48.1924 2.42268V6.25201L48 6.14095L44.5 8.16167L42.8001 7.18019V3L47.9961 0L50.1924 1.26797Z" fill="#3A8138" />
            <path d="M55.0002 0L60.1963 3V9L60.0743 9.07046L55.0002 6.14093L51.5001 8.16169L49.804 7.18244V3L55.0002 0Z" fill="#3A8138" />
            <path d="M6.19243 10.4227L8.19245 9.26798L5.99625 8L0.800098 11V17L0.924059 17.0716L6.00005 14.1409L6.19243 14.252V10.4227Z" fill="#3A8138" />
            <path d="M15.1924 9.26797L13.1924 10.4227V14.252L13 14.1409L9.50005 16.1617L7.8001 15.1802V11L12.9961 8L15.1924 9.26797Z" fill="#3A8138" />
            <path d="M22.1924 9.26797L20.1924 10.4227V14.252L20 14.1409L16.5 16.1617L14.8001 15.1802V11L19.9961 8L22.1924 9.26797Z" fill="#3A8138" />
            <path d="M29.1924 9.26797L27.1924 10.4227V14.252L27 14.1409L23.5 16.1617L21.8001 15.1802V11L26.9961 8L29.1924 9.26797Z" fill="#3A8138" />
            <path d="M36.1924 9.26797L34.1924 10.4227V14.252L34 14.1409L30.5 16.1617L28.8001 15.1802V11L33.9961 8L36.1924 9.26797Z" fill="#3A8138" />
            <path d="M43.1924 9.26797L41.1924 10.4227V14.252L41 14.1409L37.5 16.1617L35.8001 15.1802V11L40.9961 8L43.1924 9.26797Z" fill="#3A8138" />
            <path d="M50.1924 9.26797L48.1924 10.4227V14.252L48 14.1409L44.5 16.1617L42.8001 15.1802V11L47.9961 8L50.1924 9.26797Z" fill="#3A8138" />
            <path d="M60.1963 11L55.0002 8L49.804 11V15.1824L54.8062 18.0705V19.888L55.0002 20L60.1963 17V11Z" fill="#3A8138" />
            <path d="M8.1924 17.268L6.19238 18.4227V25.5774L8.19234 26.7321L5.9962 28L0.800049 25V19L5.9962 16L8.1924 17.268Z" fill="#3A8138" />
            <path d="M15.1924 17.268L13.1924 18.4227V25.5774L15.1923 26.7321L12.9962 28L7.80005 25V19L12.9962 16L15.1924 17.268Z" fill="#3A8138" />
            <path d="M22.1924 17.268L20.1924 18.4227V25.5774L22.1923 26.7321L19.9962 28L14.8 25V19L19.9962 16L22.1924 17.268Z" fill="#3A8138" />
            <path d="M29.1924 17.268L27.1924 18.4227V25.5774L29.1923 26.7321L26.9962 28L21.8 25V19L26.9962 16L29.1924 17.268Z" fill="#3A8138" />
            <path d="M36.1924 17.268L34.1924 18.4227V25.5774L36.1923 26.7321L33.9962 28L28.8 25V19L33.9962 16L36.1924 17.268Z" fill="#3A8138" />
            <path d="M43.1924 17.268L41.1924 18.4227V25.5774L43.1923 26.7321L40.9962 28L35.8 25V19L40.9962 16L43.1924 17.268Z" fill="#3A8138" />
            <path d="M48 16L53.1962 19V25L48 28L42.8039 25V19L48 16Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-member-24" width="61" height="28" viewBox="0 0 61 28">
            <path d="M8.19243 1.26797L6.19243 2.42268V6.25201L6.00005 6.14095L2.50005 8.16167L0.800098 7.18019V3L5.99614 0L8.19243 1.26797Z" fill="#3A8138" />
            <path d="M15.1924 1.26797L13.1924 2.42268V6.25201L13 6.14095L9.50005 8.16167L7.8001 7.18019V3L12.9961 0L15.1924 1.26797Z" fill="#3A8138" />
            <path d="M22.1924 1.26797L20.1924 2.42268V6.25201L20 6.14095L16.5 8.16167L14.8001 7.18019V3L19.9961 0L22.1924 1.26797Z" fill="#3A8138" />
            <path d="M29.1924 1.26797L27.1924 2.42268V6.25201L27 6.14095L23.5 8.16167L21.8001 7.18019V3L26.9961 0L29.1924 1.26797Z" fill="#3A8138" />
            <path d="M36.1924 1.26797L34.1924 2.42268V6.25201L34 6.14095L30.5 8.16167L28.8001 7.18019V3L33.9961 0L36.1924 1.26797Z" fill="#3A8138" />
            <path d="M43.1924 1.26797L41.1924 2.42268V6.25201L41 6.14095L37.5 8.16167L35.8001 7.18019V3L40.9961 0L43.1924 1.26797Z" fill="#3A8138" />
            <path d="M50.1924 1.26797L48.1924 2.42268V6.25201L48 6.14095L44.5 8.16167L42.8001 7.18019V3L47.9961 0L50.1924 1.26797Z" fill="#3A8138" />
            <path d="M55.0002 0L60.1963 3V9L60.0743 9.07046L55.0002 6.14093L51.5001 8.16169L49.804 7.18244V3L55.0002 0Z" fill="#3A8138" />
            <path d="M6.19243 10.4227L8.19245 9.26798L5.99625 8L0.800098 11V17L0.924059 17.0716L6.00005 14.1409L6.19243 14.252V10.4227Z" fill="#3A8138" />
            <path d="M15.1924 9.26797L13.1924 10.4227V14.252L13 14.1409L9.50005 16.1617L7.8001 15.1802V11L12.9961 8L15.1924 9.26797Z" fill="#3A8138" />
            <path d="M22.1924 9.26797L20.1924 10.4227V14.252L20 14.1409L16.5 16.1617L14.8001 15.1802V11L19.9961 8L22.1924 9.26797Z" fill="#3A8138" />
            <path d="M29.1924 9.26797L27.1924 10.4227V14.252L27 14.1409L23.5 16.1617L21.8001 15.1802V11L26.9961 8L29.1924 9.26797Z" fill="#3A8138" />
            <path d="M36.1924 9.26797L34.1924 10.4227V14.252L34 14.1409L30.5 16.1617L28.8001 15.1802V11L33.9961 8L36.1924 9.26797Z" fill="#3A8138" />
            <path d="M43.1924 9.26797L41.1924 10.4227V14.252L41 14.1409L37.5 16.1617L35.8001 15.1802V11L40.9961 8L43.1924 9.26797Z" fill="#3A8138" />
            <path d="M50.1924 9.26797L48.1924 10.4227V14.252L48 14.1409L44.5 16.1617L42.8001 15.1802V11L47.9961 8L50.1924 9.26797Z" fill="#3A8138" />
            <path d="M55.0002 8L60.1963 11V17L60.0743 17.0705L55.0002 14.1409L51.5001 16.1617L49.804 15.1824V11L55.0002 8Z" fill="#3A8138" />
            <path d="M8.1924 17.268L6.19238 18.4227V25.5774L8.19234 26.7321L5.9962 28L0.800049 25V19L5.9962 16L8.1924 17.268Z" fill="#3A8138" />
            <path d="M15.1924 17.268L13.1924 18.4227V25.5774L15.1923 26.7321L12.9962 28L7.80005 25V19L12.9962 16L15.1924 17.268Z" fill="#3A8138" />
            <path d="M22.1924 17.268L20.1924 18.4227V25.5774L22.1923 26.7321L19.9962 28L14.8 25V19L19.9962 16L22.1924 17.268Z" fill="#3A8138" />
            <path d="M29.1924 17.268L27.1924 18.4227V25.5774L29.1923 26.7321L26.9962 28L21.8 25V19L26.9962 16L29.1924 17.268Z" fill="#3A8138" />
            <path d="M36.1924 17.268L34.1924 18.4227V25.5774L36.1923 26.7321L33.9962 28L28.8 25V19L33.9962 16L36.1924 17.268Z" fill="#3A8138" />
            <path d="M43.1924 17.268L41.1924 18.4227V25.5774L43.1923 26.7321L40.9962 28L35.8 25V19L40.9962 16L43.1924 17.268Z" fill="#3A8138" />
            <path d="M50.1924 17.268L48.1924 18.4227V25.5774L50.1923 26.7321L47.9962 28L42.8 25V19L47.9962 16L50.1924 17.268Z" fill="#3A8138" />
            <path d="M55 16L60.1962 19V25L55 28L49.8039 25V19L55 16Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-member-3" width="26" height="12" viewBox="0 0 26 12">
            <path d="M8.1924 1.26798L6.19238 2.42269V9.57739L8.19234 10.7321L5.9962 12L0.800049 9V3L5.9962 0L8.1924 1.26798Z" fill="#3A8138" />
            <path d="M15.1924 1.26798L13.1924 2.42269V9.57739L15.1923 10.7321L12.9962 12L7.80005 9V3L12.9962 0L15.1924 1.26798Z" fill="#3A8138" />
            <path d="M20 0L25.1962 3V9L20 12L14.8039 9V3L20 0Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-member-4" width="33" height="12" viewBox="0 0 33 12">
            <path d="M8.1924 1.26798L6.19238 2.42269V9.57739L8.19234 10.7321L5.9962 12L0.800049 9V3L5.9962 0L8.1924 1.26798Z" fill="#3A8138" />
            <path d="M15.1924 1.26798L13.1924 2.42269V9.57739L15.1923 10.7321L12.9962 12L7.80005 9V3L12.9962 0L15.1924 1.26798Z" fill="#3A8138" />
            <path d="M22.1924 1.26798L20.1924 2.42269V9.57739L22.1923 10.7321L19.9962 12L14.8 9V3L19.9962 0L22.1924 1.26798Z" fill="#3A8138" />
            <path d="M27 0L32.1962 3V9L27 12L21.8039 9V3L27 0Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-member-5" width="40" height="12" viewBox="0 0 40 12">
            <path d="M8.1924 1.26798L6.19238 2.42269V9.57739L8.19234 10.7321L5.9962 12L0.800049 9V3L5.9962 0L8.1924 1.26798Z" fill="#3A8138" />
            <path d="M15.1924 1.26798L13.1924 2.42269V9.57739L15.1923 10.7321L12.9962 12L7.80005 9V3L12.9962 0L15.1924 1.26798Z" fill="#3A8138" />
            <path d="M22.1924 1.26798L20.1924 2.42269V9.57739L22.1923 10.7321L19.9962 12L14.8 9V3L19.9962 0L22.1924 1.26798Z" fill="#3A8138" />
            <path d="M29.1924 1.26798L27.1924 2.42269V9.57739L29.1923 10.7321L26.9962 12L21.8 9V3L26.9962 0L29.1924 1.26798Z" fill="#3A8138" />
            <path d="M34 0L39.1962 3V9L34 12L28.8039 9V3L34 0Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-member-6" width="47" height="12" viewBox="0 0 47 12">
            <path d="M8.1924 1.26798L6.19238 2.42269V9.57739L8.19234 10.7321L5.9962 12L0.800049 9V3L5.9962 0L8.1924 1.26798Z" fill="#3A8138" />
            <path d="M15.1924 1.26798L13.1924 2.42269V9.57739L15.1923 10.7321L12.9962 12L7.80005 9V3L12.9962 0L15.1924 1.26798Z" fill="#3A8138" />
            <path d="M22.1924 1.26798L20.1924 2.42269V9.57739L22.1923 10.7321L19.9962 12L14.8 9V3L19.9962 0L22.1924 1.26798Z" fill="#3A8138" />
            <path d="M29.1924 1.26798L27.1924 2.42269V9.57739L29.1923 10.7321L26.9962 12L21.8 9V3L26.9962 0L29.1924 1.26798Z" fill="#3A8138" />
            <path d="M36.1924 1.26798L34.1924 2.42269V9.57739L36.1923 10.7321L33.9962 12L28.8 9V3L33.9962 0L36.1924 1.26798Z" fill="#3A8138" />
            <path d="M41 0L46.1962 3V9L41 12L35.8039 9V3L41 0Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-member-7" width="54" height="12" viewBox="0 0 54 12">
            <path d="M8.1924 1.26798L6.19238 2.42269V9.57739L8.19234 10.7321L5.9962 12L0.800049 9V3L5.9962 0L8.1924 1.26798Z" fill="#3A8138" />
            <path d="M15.1924 1.26798L13.1924 2.42269V9.57739L15.1923 10.7321L12.9962 12L7.80005 9V3L12.9962 0L15.1924 1.26798Z" fill="#3A8138" />
            <path d="M22.1924 1.26798L20.1924 2.42269V9.57739L22.1923 10.7321L19.9962 12L14.8 9V3L19.9962 0L22.1924 1.26798Z" fill="#3A8138" />
            <path d="M29.1924 1.26798L27.1924 2.42269V9.57739L29.1923 10.7321L26.9962 12L21.8 9V3L26.9962 0L29.1924 1.26798Z" fill="#3A8138" />
            <path d="M36.1924 1.26798L34.1924 2.42269V9.57739L36.1923 10.7321L33.9962 12L28.8 9V3L33.9962 0L36.1924 1.26798Z" fill="#3A8138" />
            <path d="M43.1924 1.26798L41.1924 2.42269V9.57739L43.1923 10.7321L40.9962 12L35.8 9V3L40.9962 0L43.1924 1.26798Z" fill="#3A8138" />
            <path d="M48 0L53.1962 3V9L48 12L42.8039 9V3L48 0Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-member-8" width="61" height="12" viewBox="0 0 61 12">
            <path d="M8.1924 1.26798L6.19238 2.42269V9.57739L8.19234 10.7321L5.9962 12L0.800049 9V3L5.9962 0L8.1924 1.26798Z" fill="#3A8138" />
            <path d="M15.1924 1.26798L13.1924 2.42269V9.57739L15.1923 10.7321L12.9962 12L7.80005 9V3L12.9962 0L15.1924 1.26798Z" fill="#3A8138" />
            <path d="M22.1924 1.26798L20.1924 2.42269V9.57739L22.1923 10.7321L19.9962 12L14.8 9V3L19.9962 0L22.1924 1.26798Z" fill="#3A8138" />
            <path d="M29.1924 1.26798L27.1924 2.42269V9.57739L29.1923 10.7321L26.9962 12L21.8 9V3L26.9962 0L29.1924 1.26798Z" fill="#3A8138" />
            <path d="M36.1924 1.26798L34.1924 2.42269V9.57739L36.1923 10.7321L33.9962 12L28.8 9V3L33.9962 0L36.1924 1.26798Z" fill="#3A8138" />
            <path d="M43.1924 1.26798L41.1924 2.42269V9.57739L43.1923 10.7321L40.9962 12L35.8 9V3L40.9962 0L43.1924 1.26798Z" fill="#3A8138" />
            <path d="M50.1924 1.26798L48.1924 2.42269V9.57739L50.1923 10.7321L47.9962 12L42.8 9V3L47.9962 0L50.1924 1.26798Z" fill="#3A8138" />
            <path d="M55 0L60.1962 3V9L55 12L49.8039 9V3L55 0Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-member-9" width="61" height="20" viewBox="0 0 61 20">
            <path d="M6.19238 2.42269L8.1924 1.26798L5.9962 0L0.800049 3V9L0.924011 9.07157L6 6.14095L6.19238 6.25202V2.42269Z" fill="#3A8138" />
            <path d="M12.8062 11.8903V10.0705L7.80005 7.1802V3L12.9962 0L15.1924 1.26798L13.1924 2.42269V9.57739L15.1923 10.7321L12.9962 12L12.8062 11.8903Z" fill="#3A8138" />
            <path d="M22.1924 1.26798L20.1923 2.42269V9.57739L22.1923 10.7321L19.9962 12L14.8 9V3L19.9962 0L22.1924 1.26798Z" fill="#3A8138" />
            <path d="M29.1924 1.26798L27.1923 2.42269V9.57739L29.1923 10.7321L26.9962 12L21.8 9V3L26.9962 0L29.1924 1.26798Z" fill="#3A8138" />
            <path d="M36.1924 1.26798L34.1923 2.42269V9.57739L36.1923 10.7321L33.9962 12L28.8 9V3L33.9962 0L36.1924 1.26798Z" fill="#3A8138" />
            <path d="M43.1924 1.26798L41.1923 2.42269V9.57739L43.1923 10.7321L40.9962 12L35.8 9V3L40.9962 0L43.1924 1.26798Z" fill="#3A8138" />
            <path d="M50.1924 1.26798L48.1923 2.42269V9.57739L50.1923 10.7321L47.9962 12L42.8 9V3L47.9962 0L50.1924 1.26798Z" fill="#3A8138" />
            <path d="M55 0L60.1962 3V9L55 12L49.8038 9V3L55 0Z" fill="#3A8138" />
            <path d="M6 8L11.1962 11V17L6 20L0.803848 17V11L6 8Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-project-1" width="12" height="12" viewBox="0 0 12 12">
            <path d="M6 0L12 12H0L6 0Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-project-10" width="54" height="20" viewBox="0 0 54 20">
            <path d="M6 0L8.18756 4.37512L7.09317 6.5639L6 4.37756L2.18878 12H0L6 0Z" fill="#3A8138" />
            <path d="M14.1876 4.37512L12 0L7.90561 8.18878L9 10.3776L12 4.37756L13.0932 6.56391L14.1876 4.37512Z" fill="#3A8138" />
            <path d="M18 0L20.1876 4.37512L16.3751 12H15.8112L13.9056 8.18878L18 0Z" fill="#3A8138" />
            <path d="M26.1876 4.37512L24 0L18 12H22.3751L26.1876 4.37512Z" fill="#3A8138" />
            <path d="M32.1876 4.37512L30 0L24 12H28.3751L32.1876 4.37512Z" fill="#3A8138" />
            <path d="M38.1876 4.37512L36 0L30 12H34.3751L38.1876 4.37512Z" fill="#3A8138" />
            <path d="M44.1876 4.37512L42 0L36 12H40.3751L44.1876 4.37512Z" fill="#3A8138" />
            <path d="M48 0L54 12H42L48 0Z" fill="#3A8138" />
            <path d="M8.18756 12.3751L6 8L0 20H4.37512L8.18756 12.3751Z" fill="#3A8138" />
            <path d="M12 8L18 20H6L12 8Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-project-11" width="54" height="20" viewBox="0 0 54 20">
            <path d="M6 0L8.18756 4.37512L7.09317 6.5639L6 4.37756L2.18878 12H0L6 0Z" fill="#3A8138" />
            <path d="M14.1876 4.37512L12 0L7.90561 8.18878L9 10.3776L12 4.37756L13.0932 6.56391L14.1876 4.37512Z" fill="#3A8138" />
            <path d="M20.1876 4.37512L18 0L13.9056 8.18878L15 10.3776L18 4.37756L19.0932 6.56391L20.1876 4.37512Z" fill="#3A8138" />
            <path d="M24 0L26.1876 4.37512L22.3751 12H21.8112L19.9056 8.18878L24 0Z" fill="#3A8138" />
            <path d="M32.1876 4.37512L30 0L24 12H28.3751L32.1876 4.37512Z" fill="#3A8138" />
            <path d="M38.1876 4.37512L36 0L30 12H34.3751L38.1876 4.37512Z" fill="#3A8138" />
            <path d="M44.1876 4.37512L42 0L36 12H40.3751L44.1876 4.37512Z" fill="#3A8138" />
            <path d="M48 0L54 12H42L48 0Z" fill="#3A8138" />
            <path d="M8.18756 12.3751L6 8L0 20H4.37512L8.18756 12.3751Z" fill="#3A8138" />
            <path d="M14.1876 12.3751L12 8L6 20H10.3751L14.1876 12.3751Z" fill="#3A8138" />
            <path d="M18 8L24 20H12L18 8Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-project-12" width="54" height="20" viewBox="0 0 54 20">
            <path d="M6 0L8.18756 4.37512L7.09317 6.5639L6 4.37756L2.18878 12H0L6 0Z" fill="#3A8138" />
            <path d="M14.1876 4.37512L12 0L7.90561 8.18878L9 10.3776L12 4.37756L13.0932 6.56391L14.1876 4.37512Z" fill="#3A8138" />
            <path d="M20.1876 4.37512L18 0L13.9056 8.18878L15 10.3776L18 4.37756L19.0932 6.56391L20.1876 4.37512Z" fill="#3A8138" />
            <path d="M26.1876 4.37512L24 0L19.9056 8.18878L21 10.3776L24 4.37756L25.0932 6.56391L26.1876 4.37512Z" fill="#3A8138" />
            <path d="M30 0L32.1876 4.37512L28.3751 12H27.8112L25.9056 8.18878L30 0Z" fill="#3A8138" />
            <path d="M36 0L38.1876 4.37512L34.3751 12H33.8112L31.9056 8.18878L36 0Z" fill="#3A8138" />
            <path d="M44.1876 4.37512L42 0L36 12H40.3751L44.1876 4.37512Z" fill="#3A8138" />
            <path d="M48 0L54 12H42L48 0Z" fill="#3A8138" />
            <path d="M8.18756 12.3751L6 8L0 20H4.37512L8.18756 12.3751Z" fill="#3A8138" />
            <path d="M14.1876 12.3751L12 8L6 20H10.3751L14.1876 12.3751Z" fill="#3A8138" />
            <path d="M20.1876 12.3751L18 8L12 20H16.3751L20.1876 12.3751Z" fill="#3A8138" />
            <path d="M24 8L30 20H18L24 8Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-project-13" width="54" height="20" viewBox="0 0 54 20">
            <path d="M6 0L8.18756 4.37512L7.09317 6.5639L6 4.37756L2.18878 12H0L6 0Z" fill="#3A8138" />
            <path d="M14.1876 4.37512L12 0L7.90561 8.18878L9 10.3776L12 4.37756L13.0932 6.56391L14.1876 4.37512Z" fill="#3A8138" />
            <path d="M20.1876 4.37512L18 0L13.9056 8.18878L15 10.3776L18 4.37756L19.0932 6.56391L20.1876 4.37512Z" fill="#3A8138" />
            <path d="M26.1876 4.37512L24 0L19.9056 8.18878L21 10.3776L24 4.37756L25.0932 6.56391L26.1876 4.37512Z" fill="#3A8138" />
            <path d="M32.1876 4.37512L30 0L25.9056 8.18878L27 10.3776L30 4.37756L31.0932 6.56391L32.1876 4.37512Z" fill="#3A8138" />
            <path d="M36 0L38.1876 4.37512L34.3751 12H33.8112L31.9056 8.18878L36 0Z" fill="#3A8138" />
            <path d="M44.1876 4.37512L42 0L36 12H40.3751L44.1876 4.37512Z" fill="#3A8138" />
            <path d="M48 0L54 12H42L48 0Z" fill="#3A8138" />
            <path d="M8.18756 12.3751L6 8L0 20H4.37512L8.18756 12.3751Z" fill="#3A8138" />
            <path d="M14.1876 12.3751L12 8L6 20H10.3751L14.1876 12.3751Z" fill="#3A8138" />
            <path d="M20.1876 12.3751L18 8L12 20H16.3751L20.1876 12.3751Z" fill="#3A8138" />
            <path d="M26.1876 12.3751L24 8L18 20H22.3751L26.1876 12.3751Z" fill="#3A8138" />
            <path d="M30 8L36 20H24L30 8Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-project-14" width="54" height="20" viewBox="0 0 54 20">
            <path d="M6 0L8.18756 4.37512L7.09317 6.5639L6 4.37756L2.18878 12H0L6 0Z" fill="#3A8138" />
            <path d="M14.1876 4.37512L12 0L7.90561 8.18878L9 10.3776L12 4.37756L13.0932 6.56391L14.1876 4.37512Z" fill="#3A8138" />
            <path d="M20.1876 4.37512L18 0L13.9056 8.18878L15 10.3776L18 4.37756L19.0932 6.56391L20.1876 4.37512Z" fill="#3A8138" />
            <path d="M26.1876 4.37512L24 0L19.9056 8.18878L21 10.3776L24 4.37756L25.0932 6.56391L26.1876 4.37512Z" fill="#3A8138" />
            <path d="M32.1876 4.37512L30 0L25.9056 8.18878L27 10.3776L30 4.37756L31.0932 6.56391L32.1876 4.37512Z" fill="#3A8138" />
            <path d="M38.1876 4.37512L36 0L31.9056 8.18878L33 10.3776L36 4.37756L37.0932 6.56391L38.1876 4.37512Z" fill="#3A8138" />
            <path d="M42 0L44.1876 4.37512L40.3751 12H39.8112L37.9056 8.18878L42 0Z" fill="#3A8138" />
            <path d="M48 0L54 12H42L48 0Z" fill="#3A8138" />
            <path d="M8.18756 12.3751L6 8L0 20H4.37512L8.18756 12.3751Z" fill="#3A8138" />
            <path d="M14.1876 12.3751L12 8L6 20H10.3751L14.1876 12.3751Z" fill="#3A8138" />
            <path d="M20.1876 12.3751L18 8L12 20H16.3751L20.1876 12.3751Z" fill="#3A8138" />
            <path d="M26.1876 12.3751L24 8L18 20H22.3751L26.1876 12.3751Z" fill="#3A8138" />
            <path d="M32.1876 12.3751L30 8L24 20H28.3751L32.1876 12.3751Z" fill="#3A8138" />
            <path d="M36 8L42 20H30L36 8Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-project-15" width="54" height="20" viewBox="0 0 54 20">
            <path d="M6 0L8.18756 4.37512L7.09317 6.5639L6 4.37756L2.18878 12H0L6 0Z" fill="#3A8138" />
            <path d="M14.1876 4.37512L12 0L7.90561 8.18878L9 10.3776L12 4.37756L13.0932 6.56391L14.1876 4.37512Z" fill="#3A8138" />
            <path d="M20.1876 4.37512L18 0L13.9056 8.18878L15 10.3776L18 4.37756L19.0932 6.56391L20.1876 4.37512Z" fill="#3A8138" />
            <path d="M26.1876 4.37512L24 0L19.9056 8.18878L21 10.3776L24 4.37756L25.0932 6.56391L26.1876 4.37512Z" fill="#3A8138" />
            <path d="M32.1876 4.37512L30 0L25.9056 8.18878L27 10.3776L30 4.37756L31.0932 6.56391L32.1876 4.37512Z" fill="#3A8138" />
            <path d="M38.1876 4.37512L36 0L31.9056 8.18878L33 10.3776L36 4.37756L37.0932 6.56391L38.1876 4.37512Z" fill="#3A8138" />
            <path d="M44.1876 4.37512L42 0L37.9056 8.18878L39 10.3776L42 4.37756L43.0932 6.56391L44.1876 4.37512Z" fill="#3A8138" />
            <path d="M54 12L48 0L43.9056 8.18878L45.8112 12H54Z" fill="#3A8138" />
            <path d="M8.18756 12.3751L6 8L0 20H4.37512L8.18756 12.3751Z" fill="#3A8138" />
            <path d="M14.1876 12.3751L12 8L6 20H10.3751L14.1876 12.3751Z" fill="#3A8138" />
            <path d="M20.1876 12.3751L18 8L12 20H16.3751L20.1876 12.3751Z" fill="#3A8138" />
            <path d="M26.1876 12.3751L24 8L18 20H22.3751L26.1876 12.3751Z" fill="#3A8138" />
            <path d="M32.1876 12.3751L30 8L24 20H28.3751L32.1876 12.3751Z" fill="#3A8138" />
            <path d="M38.1876 12.3751L36 8L30 20H34.3751L38.1876 12.3751Z" fill="#3A8138" />
            <path d="M42 8L48 20H36L42 8Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-project-16" width="54" height="20" viewBox="0 0 54 20">
            <path d="M6 0L8.18756 4.37512L7.09317 6.5639L6 4.37756L2.18878 12H0L6 0Z" fill="#3A8138" />
            <path d="M14.1876 4.37512L12 0L7.90561 8.18878L9 10.3776L12 4.37756L13.0932 6.56391L14.1876 4.37512Z" fill="#3A8138" />
            <path d="M20.1876 4.37512L18 0L13.9056 8.18878L15 10.3776L18 4.37756L19.0932 6.56391L20.1876 4.37512Z" fill="#3A8138" />
            <path d="M26.1876 4.37512L24 0L19.9056 8.18878L21 10.3776L24 4.37756L25.0932 6.56391L26.1876 4.37512Z" fill="#3A8138" />
            <path d="M32.1876 4.37512L30 0L25.9056 8.18878L27 10.3776L30 4.37756L31.0932 6.56391L32.1876 4.37512Z" fill="#3A8138" />
            <path d="M38.1876 4.37512L36 0L31.9056 8.18878L33 10.3776L36 4.37756L37.0932 6.56391L38.1876 4.37512Z" fill="#3A8138" />
            <path d="M44.1876 4.37512L42 0L37.9056 8.18878L39 10.3776L42 4.37756L43.0932 6.56391L44.1876 4.37512Z" fill="#3A8138" />
            <path d="M54 12L48 0L43.9056 8.18878L45 10.3776L48 4.37756L51.8112 12H54Z" fill="#3A8138" />
            <path d="M8.18756 12.3751L6 8L0 20H4.37512L8.18756 12.3751Z" fill="#3A8138" />
            <path d="M14.1876 12.3751L12 8L6 20H10.3751L14.1876 12.3751Z" fill="#3A8138" />
            <path d="M20.1876 12.3751L18 8L12 20H16.3751L20.1876 12.3751Z" fill="#3A8138" />
            <path d="M26.1876 12.3751L24 8L18 20H22.3751L26.1876 12.3751Z" fill="#3A8138" />
            <path d="M32.1876 12.3751L30 8L24 20H28.3751L32.1876 12.3751Z" fill="#3A8138" />
            <path d="M38.1876 12.3751L36 8L30 20H34.3751L38.1876 12.3751Z" fill="#3A8138" />
            <path d="M44.1876 12.3751L42 8L36 20H40.3751L44.1876 12.3751Z" fill="#3A8138" />
            <path d="M48 8L54 20H42L48 8Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-project-17" width="54" height="28" viewBox="0 0 54 28">
            <path d="M6 0L8.18756 4.37512L7.09317 6.5639L6 4.37756L2.18878 12H0L6 0Z" fill="#3A8138" />
            <path d="M14.1876 4.37512L12 0L7.90561 8.18878L9 10.3776L12 4.37756L13.0932 6.56391L14.1876 4.37512Z" fill="#3A8138" />
            <path d="M20.1876 4.37512L18 0L13.9056 8.18878L15 10.3776L18 4.37756L19.0932 6.56391L20.1876 4.37512Z" fill="#3A8138" />
            <path d="M26.1876 4.37512L24 0L19.9056 8.18878L21 10.3776L24 4.37756L25.0932 6.56391L26.1876 4.37512Z" fill="#3A8138" />
            <path d="M32.1876 4.37512L30 0L25.9056 8.18878L27 10.3776L30 4.37756L31.0932 6.56391L32.1876 4.37512Z" fill="#3A8138" />
            <path d="M38.1876 4.37512L36 0L31.9056 8.18878L33 10.3776L36 4.37756L37.0932 6.56391L38.1876 4.37512Z" fill="#3A8138" />
            <path d="M44.1876 4.37512L42 0L37.9056 8.18878L39 10.3776L42 4.37756L43.0932 6.56391L44.1876 4.37512Z" fill="#3A8138" />
            <path d="M54 12L48 0L43.9056 8.18878L45 10.3776L48 4.37756L51.8112 12H54Z" fill="#3A8138" />
            <path d="M6 8L8.18756 12.3751L7.09317 14.5639L6 12.3776L2.18878 20H0L6 8Z" fill="#3A8138" />
            <path d="M12 8L14.1876 12.3751L10.3751 20H9.81122L7.90561 16.1888L12 8Z" fill="#3A8138" />
            <path d="M20.1876 12.3751L18 8L12 20H16.3751L20.1876 12.3751Z" fill="#3A8138" />
            <path d="M26.1876 12.3751L24 8L18 20H22.3751L26.1876 12.3751Z" fill="#3A8138" />
            <path d="M32.1876 12.3751L30 8L24 20H28.3751L32.1876 12.3751Z" fill="#3A8138" />
            <path d="M38.1876 12.3751L36 8L30 20H34.3751L38.1876 12.3751Z" fill="#3A8138" />
            <path d="M44.1876 12.3751L42 8L36 20H40.3751L44.1876 12.3751Z" fill="#3A8138" />
            <path d="M48 8L54 20H42L48 8Z" fill="#3A8138" />
            <path d="M6 16L12 28H0L6 16Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-project-18" width="54" height="28" viewBox="0 0 54 28">
            <path d="M6 0L8.18756 4.37512L7.09317 6.5639L6 4.37756L2.18878 12H0L6 0Z" fill="#3A8138" />
            <path d="M14.1876 4.37512L12 0L7.90561 8.18878L9 10.3776L12 4.37756L13.0932 6.56391L14.1876 4.37512Z" fill="#3A8138" />
            <path d="M20.1876 4.37512L18 0L13.9056 8.18878L15 10.3776L18 4.37756L19.0932 6.56391L20.1876 4.37512Z" fill="#3A8138" />
            <path d="M26.1876 4.37512L24 0L19.9056 8.18878L21 10.3776L24 4.37756L25.0932 6.56391L26.1876 4.37512Z" fill="#3A8138" />
            <path d="M32.1876 4.37512L30 0L25.9056 8.18878L27 10.3776L30 4.37756L31.0932 6.56391L32.1876 4.37512Z" fill="#3A8138" />
            <path d="M38.1876 4.37512L36 0L31.9056 8.18878L33 10.3776L36 4.37756L37.0932 6.56391L38.1876 4.37512Z" fill="#3A8138" />
            <path d="M44.1876 4.37512L42 0L37.9056 8.18878L39 10.3776L42 4.37756L43.0932 6.56391L44.1876 4.37512Z" fill="#3A8138" />
            <path d="M54 12L48 0L43.9056 8.18878L45 10.3776L48 4.37756L51.8112 12H54Z" fill="#3A8138" />
            <path d="M6 8L8.18756 12.3751L7.09317 14.5639L6 12.3776L2.18878 20H0L6 8Z" fill="#3A8138" />
            <path d="M14.1876 12.3751L12 8L7.90561 16.1888L9 18.3776L12 12.3776L13.0932 14.5639L14.1876 12.3751Z" fill="#3A8138" />
            <path d="M18 8L20.1876 12.3751L16.3751 20H15.8112L13.9056 16.1888L18 8Z" fill="#3A8138" />
            <path d="M26.1876 12.3751L24 8L18 20H22.3751L26.1876 12.3751Z" fill="#3A8138" />
            <path d="M32.1876 12.3751L30 8L24 20H28.3751L32.1876 12.3751Z" fill="#3A8138" />
            <path d="M38.1876 12.3751L36 8L30 20H34.3751L38.1876 12.3751Z" fill="#3A8138" />
            <path d="M44.1876 12.3751L42 8L36 20H40.3751L44.1876 12.3751Z" fill="#3A8138" />
            <path d="M48 8L54 20H42L48 8Z" fill="#3A8138" />
            <path d="M8.18756 20.3751L6 16L0 28H4.37512L8.18756 20.3751Z" fill="#3A8138" />
            <path d="M12 16L18 28H6L12 16Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-project-19" width="54" height="28" viewBox="0 0 54 28">
            <path d="M6 0L8.18756 4.37512L7.09317 6.5639L6 4.37756L2.18878 12H0L6 0Z" fill="#3A8138" />
            <path d="M14.1876 4.37512L12 0L7.90561 8.18878L9 10.3776L12 4.37756L13.0932 6.56391L14.1876 4.37512Z" fill="#3A8138" />
            <path d="M20.1876 4.37512L18 0L13.9056 8.18878L15 10.3776L18 4.37756L19.0932 6.56391L20.1876 4.37512Z" fill="#3A8138" />
            <path d="M26.1876 4.37512L24 0L19.9056 8.18878L21 10.3776L24 4.37756L25.0932 6.56391L26.1876 4.37512Z" fill="#3A8138" />
            <path d="M32.1876 4.37512L30 0L25.9056 8.18878L27 10.3776L30 4.37756L31.0932 6.56391L32.1876 4.37512Z" fill="#3A8138" />
            <path d="M38.1876 4.37512L36 0L31.9056 8.18878L33 10.3776L36 4.37756L37.0932 6.56391L38.1876 4.37512Z" fill="#3A8138" />
            <path d="M44.1876 4.37512L42 0L37.9056 8.18878L39 10.3776L42 4.37756L43.0932 6.56391L44.1876 4.37512Z" fill="#3A8138" />
            <path d="M54 12L48 0L43.9056 8.18878L45 10.3776L48 4.37756L51.8112 12H54Z" fill="#3A8138" />
            <path d="M6 8L8.18756 12.3751L7.09317 14.5639L6 12.3776L2.18878 20H0L6 8Z" fill="#3A8138" />
            <path d="M14.1876 12.3751L12 8L7.90561 16.1888L9 18.3776L12 12.3776L13.0932 14.5639L14.1876 12.3751Z" fill="#3A8138" />
            <path d="M20.1876 12.3751L18 8L13.9056 16.1888L15 18.3776L18 12.3776L19.0932 14.5639L20.1876 12.3751Z" fill="#3A8138" />
            <path d="M24 8L26.1876 12.3751L22.3751 20H21.8112L19.9056 16.1888L24 8Z" fill="#3A8138" />
            <path d="M32.1876 12.3751L30 8L24 20H28.3751L32.1876 12.3751Z" fill="#3A8138" />
            <path d="M38.1876 12.3751L36 8L30 20H34.3751L38.1876 12.3751Z" fill="#3A8138" />
            <path d="M44.1876 12.3751L42 8L36 20H40.3751L44.1876 12.3751Z" fill="#3A8138" />
            <path d="M48 8L54 20H42L48 8Z" fill="#3A8138" />
            <path d="M8.18756 20.3751L6 16L0 28H4.37512L8.18756 20.3751Z" fill="#3A8138" />
            <path d="M14.1876 20.3751L12 16L6 28H10.3751L14.1876 20.3751Z" fill="#3A8138" />
            <path d="M18 16L24 28H12L18 16Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-project-2" width="18" height="12" viewBox="0 0 18 12">
            <path d="M8.18756 4.37512L6 0L0 12H4.37512L8.18756 4.37512Z" fill="#3A8138" />
            <path d="M12 0L18 12H6L12 0Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-project-20" width="54" height="28" viewBox="0 0 54 28">
            <path d="M6 0L8.18756 4.37512L7.09317 6.5639L6 4.37756L2.18878 12H0L6 0Z" fill="#3A8138" />
            <path d="M14.1876 4.37512L12 0L7.90561 8.18878L9 10.3776L12 4.37756L13.0932 6.56391L14.1876 4.37512Z" fill="#3A8138" />
            <path d="M20.1876 4.37512L18 0L13.9056 8.18878L15 10.3776L18 4.37756L19.0932 6.56391L20.1876 4.37512Z" fill="#3A8138" />
            <path d="M26.1876 4.37512L24 0L19.9056 8.18878L21 10.3776L24 4.37756L25.0932 6.56391L26.1876 4.37512Z" fill="#3A8138" />
            <path d="M32.1876 4.37512L30 0L25.9056 8.18878L27 10.3776L30 4.37756L31.0932 6.56391L32.1876 4.37512Z" fill="#3A8138" />
            <path d="M38.1876 4.37512L36 0L31.9056 8.18878L33 10.3776L36 4.37756L37.0932 6.56391L38.1876 4.37512Z" fill="#3A8138" />
            <path d="M44.1876 4.37512L42 0L37.9056 8.18878L39 10.3776L42 4.37756L43.0932 6.56391L44.1876 4.37512Z" fill="#3A8138" />
            <path d="M54 12L48 0L43.9056 8.18878L45 10.3776L48 4.37756L51.8112 12H54Z" fill="#3A8138" />
            <path d="M6 8L8.18756 12.3751L7.09317 14.5639L6 12.3776L2.18878 20H0L6 8Z" fill="#3A8138" />
            <path d="M14.1876 12.3751L12 8L7.90561 16.1888L9 18.3776L12 12.3776L13.0932 14.5639L14.1876 12.3751Z" fill="#3A8138" />
            <path d="M20.1876 12.3751L18 8L13.9056 16.1888L15 18.3776L18 12.3776L19.0932 14.5639L20.1876 12.3751Z" fill="#3A8138" />
            <path d="M26.1876 12.3751L24 8L19.9056 16.1888L21 18.3776L24 12.3776L25.0932 14.5639L26.1876 12.3751Z" fill="#3A8138" />
            <path d="M30 8L32.1876 12.3751L28.3751 20H27.8112L25.9056 16.1888L30 8Z" fill="#3A8138" />
            <path d="M36 8L38.1876 12.3751L34.3751 20H33.8112L31.9056 16.1888L36 8Z" fill="#3A8138" />
            <path d="M44.1876 12.3751L42 8L36 20H40.3751L44.1876 12.3751Z" fill="#3A8138" />
            <path d="M48 8L54 20H42L48 8Z" fill="#3A8138" />
            <path d="M8.18756 20.3751L6 16L0 28H4.37512L8.18756 20.3751Z" fill="#3A8138" />
            <path d="M14.1876 20.3751L12 16L6 28H10.3751L14.1876 20.3751Z" fill="#3A8138" />
            <path d="M20.1876 20.3751L18 16L12 28H16.3751L20.1876 20.3751Z" fill="#3A8138" />
            <path d="M24 16L30 28H18L24 16Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-project-21" width="54" height="28" viewBox="0 0 54 28">
            <path d="M6 0L8.18756 4.37512L7.09317 6.5639L6 4.37756L2.18878 12H0L6 0Z" fill="#3A8138" />
            <path d="M14.1876 4.37512L12 0L7.90561 8.18878L9 10.3776L12 4.37756L13.0932 6.56391L14.1876 4.37512Z" fill="#3A8138" />
            <path d="M20.1876 4.37512L18 0L13.9056 8.18878L15 10.3776L18 4.37756L19.0932 6.56391L20.1876 4.37512Z" fill="#3A8138" />
            <path d="M26.1876 4.37512L24 0L19.9056 8.18878L21 10.3776L24 4.37756L25.0932 6.56391L26.1876 4.37512Z" fill="#3A8138" />
            <path d="M32.1876 4.37512L30 0L25.9056 8.18878L27 10.3776L30 4.37756L31.0932 6.56391L32.1876 4.37512Z" fill="#3A8138" />
            <path d="M38.1876 4.37512L36 0L31.9056 8.18878L33 10.3776L36 4.37756L37.0932 6.56391L38.1876 4.37512Z" fill="#3A8138" />
            <path d="M44.1876 4.37512L42 0L37.9056 8.18878L39 10.3776L42 4.37756L43.0932 6.56391L44.1876 4.37512Z" fill="#3A8138" />
            <path d="M54 12L48 0L43.9056 8.18878L45 10.3776L48 4.37756L51.8112 12H54Z" fill="#3A8138" />
            <path d="M6 8L8.18756 12.3751L7.09317 14.5639L6 12.3776L2.18878 20H0L6 8Z" fill="#3A8138" />
            <path d="M14.1876 12.3751L12 8L7.90561 16.1888L9 18.3776L12 12.3776L13.0932 14.5639L14.1876 12.3751Z" fill="#3A8138" />
            <path d="M20.1876 12.3751L18 8L13.9056 16.1888L15 18.3776L18 12.3776L19.0932 14.5639L20.1876 12.3751Z" fill="#3A8138" />
            <path d="M26.1876 12.3751L24 8L19.9056 16.1888L21 18.3776L24 12.3776L25.0932 14.5639L26.1876 12.3751Z" fill="#3A8138" />
            <path d="M32.1876 12.3751L30 8L25.9056 16.1888L27 18.3776L30 12.3776L31.0932 14.5639L32.1876 12.3751Z" fill="#3A8138" />
            <path d="M36 8L38.1876 12.3751L34.3751 20H33.8112L31.9056 16.1888L36 8Z" fill="#3A8138" />
            <path d="M44.1876 12.3751L42 8L36 20H40.3751L44.1876 12.3751Z" fill="#3A8138" />
            <path d="M48 8L54 20H42L48 8Z" fill="#3A8138" />
            <path d="M8.18756 20.3751L6 16L0 28H4.37512L8.18756 20.3751Z" fill="#3A8138" />
            <path d="M14.1876 20.3751L12 16L6 28H10.3751L14.1876 20.3751Z" fill="#3A8138" />
            <path d="M20.1876 20.3751L18 16L12 28H16.3751L20.1876 20.3751Z" fill="#3A8138" />
            <path d="M26.1876 20.3751L24 16L18 28H22.3751L26.1876 20.3751Z" fill="#3A8138" />
            <path d="M30 16L36 28H24L30 16Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-project-22" width="54" height="28" viewBox="0 0 54 28">
            <path d="M6 0L8.18756 4.37512L7.09317 6.5639L6 4.37756L2.18878 12H0L6 0Z" fill="#3A8138" />
            <path d="M14.1876 4.37512L12 0L7.90561 8.18878L9 10.3776L12 4.37756L13.0932 6.56391L14.1876 4.37512Z" fill="#3A8138" />
            <path d="M20.1876 4.37512L18 0L13.9056 8.18878L15 10.3776L18 4.37756L19.0932 6.56391L20.1876 4.37512Z" fill="#3A8138" />
            <path d="M26.1876 4.37512L24 0L19.9056 8.18878L21 10.3776L24 4.37756L25.0932 6.56391L26.1876 4.37512Z" fill="#3A8138" />
            <path d="M32.1876 4.37512L30 0L25.9056 8.18878L27 10.3776L30 4.37756L31.0932 6.56391L32.1876 4.37512Z" fill="#3A8138" />
            <path d="M38.1876 4.37512L36 0L31.9056 8.18878L33 10.3776L36 4.37756L37.0932 6.56391L38.1876 4.37512Z" fill="#3A8138" />
            <path d="M44.1876 4.37512L42 0L37.9056 8.18878L39 10.3776L42 4.37756L43.0932 6.56391L44.1876 4.37512Z" fill="#3A8138" />
            <path d="M54 12L48 0L43.9056 8.18878L45 10.3776L48 4.37756L51.8112 12H54Z" fill="#3A8138" />
            <path d="M6 8L8.18756 12.3751L7.09317 14.5639L6 12.3776L2.18878 20H0L6 8Z" fill="#3A8138" />
            <path d="M14.1876 12.3751L12 8L7.90561 16.1888L9 18.3776L12 12.3776L13.0932 14.5639L14.1876 12.3751Z" fill="#3A8138" />
            <path d="M20.1876 12.3751L18 8L13.9056 16.1888L15 18.3776L18 12.3776L19.0932 14.5639L20.1876 12.3751Z" fill="#3A8138" />
            <path d="M26.1876 12.3751L24 8L19.9056 16.1888L21 18.3776L24 12.3776L25.0932 14.5639L26.1876 12.3751Z" fill="#3A8138" />
            <path d="M32.1876 12.3751L30 8L25.9056 16.1888L27 18.3776L30 12.3776L31.0932 14.5639L32.1876 12.3751Z" fill="#3A8138" />
            <path d="M38.1876 12.3751L36 8L31.9056 16.1888L33 18.3776L36 12.3776L37.0932 14.5639L38.1876 12.3751Z" fill="#3A8138" />
            <path d="M42 8L44.1876 12.3751L40.3751 20H39.8112L37.9056 16.1888L42 8Z" fill="#3A8138" />
            <path d="M48 8L54 20H42L48 8Z" fill="#3A8138" />
            <path d="M8.18756 20.3751L6 16L0 28H4.37512L8.18756 20.3751Z" fill="#3A8138" />
            <path d="M14.1876 20.3751L12 16L6 28H10.3751L14.1876 20.3751Z" fill="#3A8138" />
            <path d="M20.1876 20.3751L18 16L12 28H16.3751L20.1876 20.3751Z" fill="#3A8138" />
            <path d="M26.1876 20.3751L24 16L18 28H22.3751L26.1876 20.3751Z" fill="#3A8138" />
            <path d="M32.1876 20.3751L30 16L24 28H28.3751L32.1876 20.3751Z" fill="#3A8138" />
            <path d="M36 16L42 28H30L36 16Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-project-23" width="54" height="28" viewBox="0 0 54 28">
            <path d="M6 0L8.18756 4.37512L7.09317 6.5639L6 4.37756L2.18878 12H0L6 0Z" fill="#3A8138" />
            <path d="M14.1876 4.37512L12 0L7.90561 8.18878L9 10.3776L12 4.37756L13.0932 6.56391L14.1876 4.37512Z" fill="#3A8138" />
            <path d="M20.1876 4.37512L18 0L13.9056 8.18878L15 10.3776L18 4.37756L19.0932 6.56391L20.1876 4.37512Z" fill="#3A8138" />
            <path d="M26.1876 4.37512L24 0L19.9056 8.18878L21 10.3776L24 4.37756L25.0932 6.56391L26.1876 4.37512Z" fill="#3A8138" />
            <path d="M32.1876 4.37512L30 0L25.9056 8.18878L27 10.3776L30 4.37756L31.0932 6.56391L32.1876 4.37512Z" fill="#3A8138" />
            <path d="M38.1876 4.37512L36 0L31.9056 8.18878L33 10.3776L36 4.37756L37.0932 6.56391L38.1876 4.37512Z" fill="#3A8138" />
            <path d="M44.1876 4.37512L42 0L37.9056 8.18878L39 10.3776L42 4.37756L43.0932 6.56391L44.1876 4.37512Z" fill="#3A8138" />
            <path d="M54 12L48 0L43.9056 8.18878L45 10.3776L48 4.37756L51.8112 12H54Z" fill="#3A8138" />
            <path d="M6 8L8.18756 12.3751L7.09317 14.5639L6 12.3776L2.18878 20H0L6 8Z" fill="#3A8138" />
            <path d="M14.1876 12.3751L12 8L7.90561 16.1888L9 18.3776L12 12.3776L13.0932 14.5639L14.1876 12.3751Z" fill="#3A8138" />
            <path d="M20.1876 12.3751L18 8L13.9056 16.1888L15 18.3776L18 12.3776L19.0932 14.5639L20.1876 12.3751Z" fill="#3A8138" />
            <path d="M26.1876 12.3751L24 8L19.9056 16.1888L21 18.3776L24 12.3776L25.0932 14.5639L26.1876 12.3751Z" fill="#3A8138" />
            <path d="M32.1876 12.3751L30 8L25.9056 16.1888L27 18.3776L30 12.3776L31.0932 14.5639L32.1876 12.3751Z" fill="#3A8138" />
            <path d="M38.1876 12.3751L36 8L31.9056 16.1888L33 18.3776L36 12.3776L37.0932 14.5639L38.1876 12.3751Z" fill="#3A8138" />
            <path d="M44.1876 12.3751L42 8L37.9056 16.1888L39 18.3776L42 12.3776L43.0932 14.5639L44.1876 12.3751Z" fill="#3A8138" />
            <path d="M54 20L48 8L43.9056 16.1888L45.8112 20H54Z" fill="#3A8138" />
            <path d="M8.18756 20.3751L6 16L0 28H4.37512L8.18756 20.3751Z" fill="#3A8138" />
            <path d="M14.1876 20.3751L12 16L6 28H10.3751L14.1876 20.3751Z" fill="#3A8138" />
            <path d="M20.1876 20.3751L18 16L12 28H16.3751L20.1876 20.3751Z" fill="#3A8138" />
            <path d="M26.1876 20.3751L24 16L18 28H22.3751L26.1876 20.3751Z" fill="#3A8138" />
            <path d="M32.1876 20.3751L30 16L24 28H28.3751L32.1876 20.3751Z" fill="#3A8138" />
            <path d="M38.1876 20.3751L36 16L30 28H34.3751L38.1876 20.3751Z" fill="#3A8138" />
            <path d="M42 16L48 28H36L42 16Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-project-24" width="54" height="28" viewBox="0 0 54 28">
            <path d="M6 0L8.18756 4.37512L7.09317 6.5639L6 4.37756L2.18878 12H0L6 0Z" fill="#3A8138" />
            <path d="M14.1876 4.37512L12 0L7.90561 8.18878L9 10.3776L12 4.37756L13.0932 6.56391L14.1876 4.37512Z" fill="#3A8138" />
            <path d="M20.1876 4.37512L18 0L13.9056 8.18878L15 10.3776L18 4.37756L19.0932 6.56391L20.1876 4.37512Z" fill="#3A8138" />
            <path d="M26.1876 4.37512L24 0L19.9056 8.18878L21 10.3776L24 4.37756L25.0932 6.56391L26.1876 4.37512Z" fill="#3A8138" />
            <path d="M32.1876 4.37512L30 0L25.9056 8.18878L27 10.3776L30 4.37756L31.0932 6.56391L32.1876 4.37512Z" fill="#3A8138" />
            <path d="M38.1876 4.37512L36 0L31.9056 8.18878L33 10.3776L36 4.37756L37.0932 6.56391L38.1876 4.37512Z" fill="#3A8138" />
            <path d="M44.1876 4.37512L42 0L37.9056 8.18878L39 10.3776L42 4.37756L43.0932 6.56391L44.1876 4.37512Z" fill="#3A8138" />
            <path d="M54 12L48 0L43.9056 8.18878L45 10.3776L48 4.37756L51.8112 12H54Z" fill="#3A8138" />
            <path d="M6 8L8.18756 12.3751L7.09317 14.5639L6 12.3776L2.18878 20H0L6 8Z" fill="#3A8138" />
            <path d="M14.1876 12.3751L12 8L7.90561 16.1888L9 18.3776L12 12.3776L13.0932 14.5639L14.1876 12.3751Z" fill="#3A8138" />
            <path d="M20.1876 12.3751L18 8L13.9056 16.1888L15 18.3776L18 12.3776L19.0932 14.5639L20.1876 12.3751Z" fill="#3A8138" />
            <path d="M26.1876 12.3751L24 8L19.9056 16.1888L21 18.3776L24 12.3776L25.0932 14.5639L26.1876 12.3751Z" fill="#3A8138" />
            <path d="M32.1876 12.3751L30 8L25.9056 16.1888L27 18.3776L30 12.3776L31.0932 14.5639L32.1876 12.3751Z" fill="#3A8138" />
            <path d="M38.1876 12.3751L36 8L31.9056 16.1888L33 18.3776L36 12.3776L37.0932 14.5639L38.1876 12.3751Z" fill="#3A8138" />
            <path d="M44.1876 12.3751L42 8L37.9056 16.1888L39 18.3776L42 12.3776L43.0932 14.5639L44.1876 12.3751Z" fill="#3A8138" />
            <path d="M54 20L48 8L43.9056 16.1888L45 18.3776L48 12.3776L51.8112 20H54Z" fill="#3A8138" />
            <path d="M8.18756 20.3751L6 16L0 28H4.37512L8.18756 20.3751Z" fill="#3A8138" />
            <path d="M14.1876 20.3751L12 16L6 28H10.3751L14.1876 20.3751Z" fill="#3A8138" />
            <path d="M20.1876 20.3751L18 16L12 28H16.3751L20.1876 20.3751Z" fill="#3A8138" />
            <path d="M26.1876 20.3751L24 16L18 28H22.3751L26.1876 20.3751Z" fill="#3A8138" />
            <path d="M32.1876 20.3751L30 16L24 28H28.3751L32.1876 20.3751Z" fill="#3A8138" />
            <path d="M38.1876 20.3751L36 16L30 28H34.3751L38.1876 20.3751Z" fill="#3A8138" />
            <path d="M44.1876 20.3751L42 16L36 28H40.3751L44.1876 20.3751Z" fill="#3A8138" />
            <path d="M48 16L54 28H42L48 16Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-project-25" width="54" height="36" viewBox="0 0 54 36">
            <path d="M6 0L8.18756 4.37512L7.09317 6.5639L6 4.37756L2.18878 12H0L6 0Z" fill="#3A8138" />
            <path d="M14.1876 4.37512L12 0L7.90561 8.18878L9 10.3776L12 4.37756L13.0932 6.56391L14.1876 4.37512Z" fill="#3A8138" />
            <path d="M20.1876 4.37512L18 0L13.9056 8.18878L15 10.3776L18 4.37756L19.0932 6.56391L20.1876 4.37512Z" fill="#3A8138" />
            <path d="M26.1876 4.37512L24 0L19.9056 8.18878L21 10.3776L24 4.37756L25.0932 6.56391L26.1876 4.37512Z" fill="#3A8138" />
            <path d="M32.1876 4.37512L30 0L25.9056 8.18878L27 10.3776L30 4.37756L31.0932 6.56391L32.1876 4.37512Z" fill="#3A8138" />
            <path d="M38.1876 4.37512L36 0L31.9056 8.18878L33 10.3776L36 4.37756L37.0932 6.56391L38.1876 4.37512Z" fill="#3A8138" />
            <path d="M44.1876 4.37512L42 0L37.9056 8.18878L39 10.3776L42 4.37756L43.0932 6.56391L44.1876 4.37512Z" fill="#3A8138" />
            <path d="M54 12L48 0L43.9056 8.18878L45 10.3776L48 4.37756L51.8112 12H54Z" fill="#3A8138" />
            <path d="M6 8L8.18756 12.3751L7.09317 14.5639L6 12.3776L2.18878 20H0L6 8Z" fill="#3A8138" />
            <path d="M14.1876 12.3751L12 8L7.90561 16.1888L9 18.3776L12 12.3776L13.0932 14.5639L14.1876 12.3751Z" fill="#3A8138" />
            <path d="M20.1876 12.3751L18 8L13.9056 16.1888L15 18.3776L18 12.3776L19.0932 14.5639L20.1876 12.3751Z" fill="#3A8138" />
            <path d="M26.1876 12.3751L24 8L19.9056 16.1888L21 18.3776L24 12.3776L25.0932 14.5639L26.1876 12.3751Z" fill="#3A8138" />
            <path d="M32.1876 12.3751L30 8L25.9056 16.1888L27 18.3776L30 12.3776L31.0932 14.5639L32.1876 12.3751Z" fill="#3A8138" />
            <path d="M38.1876 12.3751L36 8L31.9056 16.1888L33 18.3776L36 12.3776L37.0932 14.5639L38.1876 12.3751Z" fill="#3A8138" />
            <path d="M44.1876 12.3751L42 8L37.9056 16.1888L39 18.3776L42 12.3776L43.0932 14.5639L44.1876 12.3751Z" fill="#3A8138" />
            <path d="M54 20L48 8L43.9056 16.1888L45 18.3776L48 12.3776L51.8112 20H54Z" fill="#3A8138" />
            <path d="M6 16L8.18756 20.3751L7.09317 22.5639L6 20.3776L2.18878 28H0L6 16Z" fill="#3A8138" />
            <path d="M12 16L14.1876 20.3751L10.3751 28H9.81122L7.90561 24.1888L12 16Z" fill="#3A8138" />
            <path d="M20.1876 20.3751L18 16L12 28H16.3751L20.1876 20.3751Z" fill="#3A8138" />
            <path d="M26.1876 20.3751L24 16L18 28H22.3751L26.1876 20.3751Z" fill="#3A8138" />
            <path d="M32.1876 20.3751L30 16L24 28H28.3751L32.1876 20.3751Z" fill="#3A8138" />
            <path d="M38.1876 20.3751L36 16L30 28H34.3751L38.1876 20.3751Z" fill="#3A8138" />
            <path d="M44.1876 20.3751L42 16L36 28H40.3751L44.1876 20.3751Z" fill="#3A8138" />
            <path d="M48 16L54 28H42L48 16Z" fill="#3A8138" />
            <path d="M6 24L12 36H0L6 24Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-project-26" width="54" height="36" viewBox="0 0 54 36">
            <path d="M6 0L8.18756 4.37512L7.09317 6.5639L6 4.37756L2.18878 12H0L6 0Z" fill="#3A8138" />
            <path d="M14.1876 4.37512L12 0L7.90561 8.18878L9 10.3776L12 4.37756L13.0932 6.56391L14.1876 4.37512Z" fill="#3A8138" />
            <path d="M20.1876 4.37512L18 0L13.9056 8.18878L15 10.3776L18 4.37756L19.0932 6.56391L20.1876 4.37512Z" fill="#3A8138" />
            <path d="M26.1876 4.37512L24 0L19.9056 8.18878L21 10.3776L24 4.37756L25.0932 6.56391L26.1876 4.37512Z" fill="#3A8138" />
            <path d="M32.1876 4.37512L30 0L25.9056 8.18878L27 10.3776L30 4.37756L31.0932 6.56391L32.1876 4.37512Z" fill="#3A8138" />
            <path d="M38.1876 4.37512L36 0L31.9056 8.18878L33 10.3776L36 4.37756L37.0932 6.56391L38.1876 4.37512Z" fill="#3A8138" />
            <path d="M44.1876 4.37512L42 0L37.9056 8.18878L39 10.3776L42 4.37756L43.0932 6.56391L44.1876 4.37512Z" fill="#3A8138" />
            <path d="M54 12L48 0L43.9056 8.18878L45 10.3776L48 4.37756L51.8112 12H54Z" fill="#3A8138" />
            <path d="M6 8L8.18756 12.3751L7.09317 14.5639L6 12.3776L2.18878 20H0L6 8Z" fill="#3A8138" />
            <path d="M14.1876 12.3751L12 8L7.90561 16.1888L9 18.3776L12 12.3776L13.0932 14.5639L14.1876 12.3751Z" fill="#3A8138" />
            <path d="M20.1876 12.3751L18 8L13.9056 16.1888L15 18.3776L18 12.3776L19.0932 14.5639L20.1876 12.3751Z" fill="#3A8138" />
            <path d="M26.1876 12.3751L24 8L19.9056 16.1888L21 18.3776L24 12.3776L25.0932 14.5639L26.1876 12.3751Z" fill="#3A8138" />
            <path d="M32.1876 12.3751L30 8L25.9056 16.1888L27 18.3776L30 12.3776L31.0932 14.5639L32.1876 12.3751Z" fill="#3A8138" />
            <path d="M38.1876 12.3751L36 8L31.9056 16.1888L33 18.3776L36 12.3776L37.0932 14.5639L38.1876 12.3751Z" fill="#3A8138" />
            <path d="M44.1876 12.3751L42 8L37.9056 16.1888L39 18.3776L42 12.3776L43.0932 14.5639L44.1876 12.3751Z" fill="#3A8138" />
            <path d="M54 20L48 8L43.9056 16.1888L45 18.3776L48 12.3776L51.8112 20H54Z" fill="#3A8138" />
            <path d="M6 16L8.18756 20.3751L7.09317 22.5639L6 20.3776L2.18878 28H0L6 16Z" fill="#3A8138" />
            <path d="M14.1876 20.3751L12 16L7.90561 24.1888L9 26.3776L12 20.3776L13.0932 22.5639L14.1876 20.3751Z" fill="#3A8138" />
            <path d="M18 16L20.1876 20.3751L16.3751 28H15.8112L13.9056 24.1888L18 16Z" fill="#3A8138" />
            <path d="M26.1876 20.3751L24 16L18 28H22.3751L26.1876 20.3751Z" fill="#3A8138" />
            <path d="M32.1876 20.3751L30 16L24 28H28.3751L32.1876 20.3751Z" fill="#3A8138" />
            <path d="M38.1876 20.3751L36 16L30 28H34.3751L38.1876 20.3751Z" fill="#3A8138" />
            <path d="M44.1876 20.3751L42 16L36 28H40.3751L44.1876 20.3751Z" fill="#3A8138" />
            <path d="M48 16L54 28H42L48 16Z" fill="#3A8138" />
            <path d="M8.18756 28.3751L6 24L0 36H4.37512L8.18756 28.3751Z" fill="#3A8138" />
            <path d="M12 24L18 36H6L12 24Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-project-27" width="54" height="36" viewBox="0 0 54 36">
            <path d="M6 0L8.18756 4.37512L7.09317 6.5639L6 4.37756L2.18878 12H0L6 0Z" fill="#3A8138" />
            <path d="M14.1876 4.37512L12 0L7.90561 8.18878L9 10.3776L12 4.37756L13.0932 6.56391L14.1876 4.37512Z" fill="#3A8138" />
            <path d="M20.1876 4.37512L18 0L13.9056 8.18878L15 10.3776L18 4.37756L19.0932 6.56391L20.1876 4.37512Z" fill="#3A8138" />
            <path d="M26.1876 4.37512L24 0L19.9056 8.18878L21 10.3776L24 4.37756L25.0932 6.56391L26.1876 4.37512Z" fill="#3A8138" />
            <path d="M32.1876 4.37512L30 0L25.9056 8.18878L27 10.3776L30 4.37756L31.0932 6.56391L32.1876 4.37512Z" fill="#3A8138" />
            <path d="M38.1876 4.37512L36 0L31.9056 8.18878L33 10.3776L36 4.37756L37.0932 6.56391L38.1876 4.37512Z" fill="#3A8138" />
            <path d="M44.1876 4.37512L42 0L37.9056 8.18878L39 10.3776L42 4.37756L43.0932 6.56391L44.1876 4.37512Z" fill="#3A8138" />
            <path d="M54 12L48 0L43.9056 8.18878L45 10.3776L48 4.37756L51.8112 12H54Z" fill="#3A8138" />
            <path d="M6 8L8.18756 12.3751L7.09317 14.5639L6 12.3776L2.18878 20H0L6 8Z" fill="#3A8138" />
            <path d="M14.1876 12.3751L12 8L7.90561 16.1888L9 18.3776L12 12.3776L13.0932 14.5639L14.1876 12.3751Z" fill="#3A8138" />
            <path d="M20.1876 12.3751L18 8L13.9056 16.1888L15 18.3776L18 12.3776L19.0932 14.5639L20.1876 12.3751Z" fill="#3A8138" />
            <path d="M26.1876 12.3751L24 8L19.9056 16.1888L21 18.3776L24 12.3776L25.0932 14.5639L26.1876 12.3751Z" fill="#3A8138" />
            <path d="M32.1876 12.3751L30 8L25.9056 16.1888L27 18.3776L30 12.3776L31.0932 14.5639L32.1876 12.3751Z" fill="#3A8138" />
            <path d="M38.1876 12.3751L36 8L31.9056 16.1888L33 18.3776L36 12.3776L37.0932 14.5639L38.1876 12.3751Z" fill="#3A8138" />
            <path d="M44.1876 12.3751L42 8L37.9056 16.1888L39 18.3776L42 12.3776L43.0932 14.5639L44.1876 12.3751Z" fill="#3A8138" />
            <path d="M54 20L48 8L43.9056 16.1888L45 18.3776L48 12.3776L51.8112 20H54Z" fill="#3A8138" />
            <path d="M6 16L8.18756 20.3751L7.09317 22.5639L6 20.3776L2.18878 28H0L6 16Z" fill="#3A8138" />
            <path d="M14.1876 20.3751L12 16L7.90561 24.1888L9 26.3776L12 20.3776L13.0932 22.5639L14.1876 20.3751Z" fill="#3A8138" />
            <path d="M20.1876 20.3751L18 16L13.9056 24.1888L15 26.3776L18 20.3776L19.0932 22.5639L20.1876 20.3751Z" fill="#3A8138" />
            <path d="M24 16L26.1876 20.3751L22.3751 28H21.8112L19.9056 24.1888L24 16Z" fill="#3A8138" />
            <path d="M32.1876 20.3751L30 16L24 28H28.3751L32.1876 20.3751Z" fill="#3A8138" />
            <path d="M38.1876 20.3751L36 16L30 28H34.3751L38.1876 20.3751Z" fill="#3A8138" />
            <path d="M44.1876 20.3751L42 16L36 28H40.3751L44.1876 20.3751Z" fill="#3A8138" />
            <path d="M48 16L54 28H42L48 16Z" fill="#3A8138" />
            <path d="M8.18756 28.3751L6 24L0 36H4.37512L8.18756 28.3751Z" fill="#3A8138" />
            <path d="M14.1876 28.3751L12 24L6 36H10.3751L14.1876 28.3751Z" fill="#3A8138" />
            <path d="M18 24L24 36H12L18 24Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-project-28" width="54" height="36" viewBox="0 0 54 36">
            <path d="M6 0L8.18756 4.37512L7.09317 6.5639L6 4.37756L2.18878 12H0L6 0Z" fill="#3A8138" />
            <path d="M14.1876 4.37512L12 0L7.90561 8.18878L9 10.3776L12 4.37756L13.0932 6.56391L14.1876 4.37512Z" fill="#3A8138" />
            <path d="M20.1876 4.37512L18 0L13.9056 8.18878L15 10.3776L18 4.37756L19.0932 6.56391L20.1876 4.37512Z" fill="#3A8138" />
            <path d="M26.1876 4.37512L24 0L19.9056 8.18878L21 10.3776L24 4.37756L25.0932 6.56391L26.1876 4.37512Z" fill="#3A8138" />
            <path d="M32.1876 4.37512L30 0L25.9056 8.18878L27 10.3776L30 4.37756L31.0932 6.56391L32.1876 4.37512Z" fill="#3A8138" />
            <path d="M38.1876 4.37512L36 0L31.9056 8.18878L33 10.3776L36 4.37756L37.0932 6.56391L38.1876 4.37512Z" fill="#3A8138" />
            <path d="M44.1876 4.37512L42 0L37.9056 8.18878L39 10.3776L42 4.37756L43.0932 6.56391L44.1876 4.37512Z" fill="#3A8138" />
            <path d="M54 12L48 0L43.9056 8.18878L45 10.3776L48 4.37756L51.8112 12H54Z" fill="#3A8138" />
            <path d="M6 8L8.18756 12.3751L7.09317 14.5639L6 12.3776L2.18878 20H0L6 8Z" fill="#3A8138" />
            <path d="M14.1876 12.3751L12 8L7.90561 16.1888L9 18.3776L12 12.3776L13.0932 14.5639L14.1876 12.3751Z" fill="#3A8138" />
            <path d="M20.1876 12.3751L18 8L13.9056 16.1888L15 18.3776L18 12.3776L19.0932 14.5639L20.1876 12.3751Z" fill="#3A8138" />
            <path d="M26.1876 12.3751L24 8L19.9056 16.1888L21 18.3776L24 12.3776L25.0932 14.5639L26.1876 12.3751Z" fill="#3A8138" />
            <path d="M32.1876 12.3751L30 8L25.9056 16.1888L27 18.3776L30 12.3776L31.0932 14.5639L32.1876 12.3751Z" fill="#3A8138" />
            <path d="M38.1876 12.3751L36 8L31.9056 16.1888L33 18.3776L36 12.3776L37.0932 14.5639L38.1876 12.3751Z" fill="#3A8138" />
            <path d="M44.1876 12.3751L42 8L37.9056 16.1888L39 18.3776L42 12.3776L43.0932 14.5639L44.1876 12.3751Z" fill="#3A8138" />
            <path d="M54 20L48 8L43.9056 16.1888L45 18.3776L48 12.3776L51.8112 20H54Z" fill="#3A8138" />
            <path d="M6 16L8.18756 20.3751L7.09317 22.5639L6 20.3776L2.18878 28H0L6 16Z" fill="#3A8138" />
            <path d="M14.1876 20.3751L12 16L7.90561 24.1888L9 26.3776L12 20.3776L13.0932 22.5639L14.1876 20.3751Z" fill="#3A8138" />
            <path d="M20.1876 20.3751L18 16L13.9056 24.1888L15 26.3776L18 20.3776L19.0932 22.5639L20.1876 20.3751Z" fill="#3A8138" />
            <path d="M26.1876 20.3751L24 16L19.9056 24.1888L21 26.3776L24 20.3776L25.0932 22.5639L26.1876 20.3751Z" fill="#3A8138" />
            <path d="M30 16L32.1876 20.3751L28.3751 28H27.8112L25.9056 24.1888L30 16Z" fill="#3A8138" />
            <path d="M36 16L38.1876 20.3751L34.3751 28H33.8112L31.9056 24.1888L36 16Z" fill="#3A8138" />
            <path d="M44.1876 20.3751L42 16L36 28H40.3751L44.1876 20.3751Z" fill="#3A8138" />
            <path d="M48 16L54 28H42L48 16Z" fill="#3A8138" />
            <path d="M8.18756 28.3751L6 24L0 36H4.37512L8.18756 28.3751Z" fill="#3A8138" />
            <path d="M14.1876 28.3751L12 24L6 36H10.3751L14.1876 28.3751Z" fill="#3A8138" />
            <path d="M20.1876 28.3751L18 24L12 36H16.3751L20.1876 28.3751Z" fill="#3A8138" />
            <path d="M24 24L30 36H18L24 24Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-project-29" width="54" height="36" viewBox="0 0 54 36">
            <path d="M6 0L8.18756 4.37512L7.09317 6.5639L6 4.37756L2.18878 12H0L6 0Z" fill="#3A8138" />
            <path d="M14.1876 4.37512L12 0L7.90561 8.18878L9 10.3776L12 4.37756L13.0932 6.56391L14.1876 4.37512Z" fill="#3A8138" />
            <path d="M20.1876 4.37512L18 0L13.9056 8.18878L15 10.3776L18 4.37756L19.0932 6.56391L20.1876 4.37512Z" fill="#3A8138" />
            <path d="M26.1876 4.37512L24 0L19.9056 8.18878L21 10.3776L24 4.37756L25.0932 6.56391L26.1876 4.37512Z" fill="#3A8138" />
            <path d="M32.1876 4.37512L30 0L25.9056 8.18878L27 10.3776L30 4.37756L31.0932 6.56391L32.1876 4.37512Z" fill="#3A8138" />
            <path d="M38.1876 4.37512L36 0L31.9056 8.18878L33 10.3776L36 4.37756L37.0932 6.56391L38.1876 4.37512Z" fill="#3A8138" />
            <path d="M44.1876 4.37512L42 0L37.9056 8.18878L39 10.3776L42 4.37756L43.0932 6.56391L44.1876 4.37512Z" fill="#3A8138" />
            <path d="M54 12L48 0L43.9056 8.18878L45 10.3776L48 4.37756L51.8112 12H54Z" fill="#3A8138" />
            <path d="M6 8L8.18756 12.3751L7.09317 14.5639L6 12.3776L2.18878 20H0L6 8Z" fill="#3A8138" />
            <path d="M14.1876 12.3751L12 8L7.90561 16.1888L9 18.3776L12 12.3776L13.0932 14.5639L14.1876 12.3751Z" fill="#3A8138" />
            <path d="M20.1876 12.3751L18 8L13.9056 16.1888L15 18.3776L18 12.3776L19.0932 14.5639L20.1876 12.3751Z" fill="#3A8138" />
            <path d="M26.1876 12.3751L24 8L19.9056 16.1888L21 18.3776L24 12.3776L25.0932 14.5639L26.1876 12.3751Z" fill="#3A8138" />
            <path d="M32.1876 12.3751L30 8L25.9056 16.1888L27 18.3776L30 12.3776L31.0932 14.5639L32.1876 12.3751Z" fill="#3A8138" />
            <path d="M38.1876 12.3751L36 8L31.9056 16.1888L33 18.3776L36 12.3776L37.0932 14.5639L38.1876 12.3751Z" fill="#3A8138" />
            <path d="M44.1876 12.3751L42 8L37.9056 16.1888L39 18.3776L42 12.3776L43.0932 14.5639L44.1876 12.3751Z" fill="#3A8138" />
            <path d="M54 20L48 8L43.9056 16.1888L45 18.3776L48 12.3776L51.8112 20H54Z" fill="#3A8138" />
            <path d="M6 16L8.18756 20.3751L7.09317 22.5639L6 20.3776L2.18878 28H0L6 16Z" fill="#3A8138" />
            <path d="M14.1876 20.3751L12 16L7.90561 24.1888L9 26.3776L12 20.3776L13.0932 22.5639L14.1876 20.3751Z" fill="#3A8138" />
            <path d="M20.1876 20.3751L18 16L13.9056 24.1888L15 26.3776L18 20.3776L19.0932 22.5639L20.1876 20.3751Z" fill="#3A8138" />
            <path d="M26.1876 20.3751L24 16L19.9056 24.1888L21 26.3776L24 20.3776L25.0932 22.5639L26.1876 20.3751Z" fill="#3A8138" />
            <path d="M32.1876 20.3751L30 16L25.9056 24.1888L27 26.3776L30 20.3776L31.0932 22.5639L32.1876 20.3751Z" fill="#3A8138" />
            <path d="M36 16L38.1876 20.3751L34.3751 28H33.8112L31.9056 24.1888L36 16Z" fill="#3A8138" />
            <path d="M44.1876 20.3751L42 16L36 28H40.3751L44.1876 20.3751Z" fill="#3A8138" />
            <path d="M48 16L54 28H42L48 16Z" fill="#3A8138" />
            <path d="M8.18756 28.3751L6 24L0 36H4.37512L8.18756 28.3751Z" fill="#3A8138" />
            <path d="M14.1876 28.3751L12 24L6 36H10.3751L14.1876 28.3751Z" fill="#3A8138" />
            <path d="M20.1876 28.3751L18 24L12 36H16.3751L20.1876 28.3751Z" fill="#3A8138" />
            <path d="M26.1876 28.3751L24 24L18 36H22.3751L26.1876 28.3751Z" fill="#3A8138" />
            <path d="M30 24L36 36H24L30 24Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-project-3" width="24" height="12" viewBox="0 0 24 12">
            <path d="M8.18756 4.37512L6 0L0 12H4.37512L8.18756 4.37512Z" fill="#3A8138" />
            <path d="M14.1876 4.37512L12 0L6 12H10.3751L14.1876 4.37512Z" fill="#3A8138" />
            <path d="M18 0L24 12H12L18 0Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-project-30" width="54" height="36" viewBox="0 0 54 36">
            <path d="M6 0L8.18756 4.37512L7.09317 6.5639L6 4.37756L2.18878 12H0L6 0Z" fill="#3A8138" />
            <path d="M14.1876 4.37512L12 0L7.90561 8.18878L9 10.3776L12 4.37756L13.0932 6.56391L14.1876 4.37512Z" fill="#3A8138" />
            <path d="M20.1876 4.37512L18 0L13.9056 8.18878L15 10.3776L18 4.37756L19.0932 6.56391L20.1876 4.37512Z" fill="#3A8138" />
            <path d="M26.1876 4.37512L24 0L19.9056 8.18878L21 10.3776L24 4.37756L25.0932 6.56391L26.1876 4.37512Z" fill="#3A8138" />
            <path d="M32.1876 4.37512L30 0L25.9056 8.18878L27 10.3776L30 4.37756L31.0932 6.56391L32.1876 4.37512Z" fill="#3A8138" />
            <path d="M38.1876 4.37512L36 0L31.9056 8.18878L33 10.3776L36 4.37756L37.0932 6.56391L38.1876 4.37512Z" fill="#3A8138" />
            <path d="M44.1876 4.37512L42 0L37.9056 8.18878L39 10.3776L42 4.37756L43.0932 6.56391L44.1876 4.37512Z" fill="#3A8138" />
            <path d="M54 12L48 0L43.9056 8.18878L45 10.3776L48 4.37756L51.8112 12H54Z" fill="#3A8138" />
            <path d="M6 8L8.18756 12.3751L7.09317 14.5639L6 12.3776L2.18878 20H0L6 8Z" fill="#3A8138" />
            <path d="M14.1876 12.3751L12 8L7.90561 16.1888L9 18.3776L12 12.3776L13.0932 14.5639L14.1876 12.3751Z" fill="#3A8138" />
            <path d="M20.1876 12.3751L18 8L13.9056 16.1888L15 18.3776L18 12.3776L19.0932 14.5639L20.1876 12.3751Z" fill="#3A8138" />
            <path d="M26.1876 12.3751L24 8L19.9056 16.1888L21 18.3776L24 12.3776L25.0932 14.5639L26.1876 12.3751Z" fill="#3A8138" />
            <path d="M32.1876 12.3751L30 8L25.9056 16.1888L27 18.3776L30 12.3776L31.0932 14.5639L32.1876 12.3751Z" fill="#3A8138" />
            <path d="M38.1876 12.3751L36 8L31.9056 16.1888L33 18.3776L36 12.3776L37.0932 14.5639L38.1876 12.3751Z" fill="#3A8138" />
            <path d="M44.1876 12.3751L42 8L37.9056 16.1888L39 18.3776L42 12.3776L43.0932 14.5639L44.1876 12.3751Z" fill="#3A8138" />
            <path d="M54 20L48 8L43.9056 16.1888L45 18.3776L48 12.3776L51.8112 20H54Z" fill="#3A8138" />
            <path d="M6 16L8.18756 20.3751L7.09317 22.5639L6 20.3776L2.18878 28H0L6 16Z" fill="#3A8138" />
            <path d="M14.1876 20.3751L12 16L7.90561 24.1888L9 26.3776L12 20.3776L13.0932 22.5639L14.1876 20.3751Z" fill="#3A8138" />
            <path d="M20.1876 20.3751L18 16L13.9056 24.1888L15 26.3776L18 20.3776L19.0932 22.5639L20.1876 20.3751Z" fill="#3A8138" />
            <path d="M26.1876 20.3751L24 16L19.9056 24.1888L21 26.3776L24 20.3776L25.0932 22.5639L26.1876 20.3751Z" fill="#3A8138" />
            <path d="M32.1876 20.3751L30 16L25.9056 24.1888L27 26.3776L30 20.3776L31.0932 22.5639L32.1876 20.3751Z" fill="#3A8138" />
            <path d="M38.1876 20.3751L36 16L31.9056 24.1888L33 26.3776L36 20.3776L37.0932 22.5639L38.1876 20.3751Z" fill="#3A8138" />
            <path d="M42 16L44.1876 20.3751L40.3751 28H39.8112L37.9056 24.1888L42 16Z" fill="#3A8138" />
            <path d="M48 16L54 28H42L48 16Z" fill="#3A8138" />
            <path d="M8.18756 28.3751L6 24L0 36H4.37512L8.18756 28.3751Z" fill="#3A8138" />
            <path d="M14.1876 28.3751L12 24L6 36H10.3751L14.1876 28.3751Z" fill="#3A8138" />
            <path d="M20.1876 28.3751L18 24L12 36H16.3751L20.1876 28.3751Z" fill="#3A8138" />
            <path d="M26.1876 28.3751L24 24L18 36H22.3751L26.1876 28.3751Z" fill="#3A8138" />
            <path d="M32.1876 28.3751L30 24L24 36H28.3751L32.1876 28.3751Z" fill="#3A8138" />
            <path d="M36 24L42 36H30L36 24Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-project-31" width="54" height="36" viewBox="0 0 54 36">
            <path d="M6 0L8.18756 4.37512L7.09317 6.5639L6 4.37756L2.18878 12H0L6 0Z" fill="#3A8138" />
            <path d="M14.1876 4.37512L12 0L7.90561 8.18878L9 10.3776L12 4.37756L13.0932 6.56391L14.1876 4.37512Z" fill="#3A8138" />
            <path d="M20.1876 4.37512L18 0L13.9056 8.18878L15 10.3776L18 4.37756L19.0932 6.56391L20.1876 4.37512Z" fill="#3A8138" />
            <path d="M26.1876 4.37512L24 0L19.9056 8.18878L21 10.3776L24 4.37756L25.0932 6.56391L26.1876 4.37512Z" fill="#3A8138" />
            <path d="M32.1876 4.37512L30 0L25.9056 8.18878L27 10.3776L30 4.37756L31.0932 6.56391L32.1876 4.37512Z" fill="#3A8138" />
            <path d="M38.1876 4.37512L36 0L31.9056 8.18878L33 10.3776L36 4.37756L37.0932 6.56391L38.1876 4.37512Z" fill="#3A8138" />
            <path d="M44.1876 4.37512L42 0L37.9056 8.18878L39 10.3776L42 4.37756L43.0932 6.56391L44.1876 4.37512Z" fill="#3A8138" />
            <path d="M54 12L48 0L43.9056 8.18878L45 10.3776L48 4.37756L51.8112 12H54Z" fill="#3A8138" />
            <path d="M6 8L8.18756 12.3751L7.09317 14.5639L6 12.3776L2.18878 20H0L6 8Z" fill="#3A8138" />
            <path d="M14.1876 12.3751L12 8L7.90561 16.1888L9 18.3776L12 12.3776L13.0932 14.5639L14.1876 12.3751Z" fill="#3A8138" />
            <path d="M20.1876 12.3751L18 8L13.9056 16.1888L15 18.3776L18 12.3776L19.0932 14.5639L20.1876 12.3751Z" fill="#3A8138" />
            <path d="M26.1876 12.3751L24 8L19.9056 16.1888L21 18.3776L24 12.3776L25.0932 14.5639L26.1876 12.3751Z" fill="#3A8138" />
            <path d="M32.1876 12.3751L30 8L25.9056 16.1888L27 18.3776L30 12.3776L31.0932 14.5639L32.1876 12.3751Z" fill="#3A8138" />
            <path d="M38.1876 12.3751L36 8L31.9056 16.1888L33 18.3776L36 12.3776L37.0932 14.5639L38.1876 12.3751Z" fill="#3A8138" />
            <path d="M44.1876 12.3751L42 8L37.9056 16.1888L39 18.3776L42 12.3776L43.0932 14.5639L44.1876 12.3751Z" fill="#3A8138" />
            <path d="M54 20L48 8L43.9056 16.1888L45 18.3776L48 12.3776L51.8112 20H54Z" fill="#3A8138" />
            <path d="M6 16L8.18756 20.3751L7.09317 22.5639L6 20.3776L2.18878 28H0L6 16Z" fill="#3A8138" />
            <path d="M14.1876 20.3751L12 16L7.90561 24.1888L9 26.3776L12 20.3776L13.0932 22.5639L14.1876 20.3751Z" fill="#3A8138" />
            <path d="M20.1876 20.3751L18 16L13.9056 24.1888L15 26.3776L18 20.3776L19.0932 22.5639L20.1876 20.3751Z" fill="#3A8138" />
            <path d="M26.1876 20.3751L24 16L19.9056 24.1888L21 26.3776L24 20.3776L25.0932 22.5639L26.1876 20.3751Z" fill="#3A8138" />
            <path d="M32.1876 20.3751L30 16L25.9056 24.1888L27 26.3776L30 20.3776L31.0932 22.5639L32.1876 20.3751Z" fill="#3A8138" />
            <path d="M38.1876 20.3751L36 16L31.9056 24.1888L33 26.3776L36 20.3776L37.0932 22.5639L38.1876 20.3751Z" fill="#3A8138" />
            <path d="M44.1876 20.3751L42 16L37.9056 24.1888L39 26.3776L42 20.3776L43.0932 22.5639L44.1876 20.3751Z" fill="#3A8138" />
            <path d="M54 28L48 16L43.9056 24.1888L45.8112 28H54Z" fill="#3A8138" />
            <path d="M8.18756 28.3751L6 24L0 36H4.37512L8.18756 28.3751Z" fill="#3A8138" />
            <path d="M14.1876 28.3751L12 24L6 36H10.3751L14.1876 28.3751Z" fill="#3A8138" />
            <path d="M20.1876 28.3751L18 24L12 36H16.3751L20.1876 28.3751Z" fill="#3A8138" />
            <path d="M26.1876 28.3751L24 24L18 36H22.3751L26.1876 28.3751Z" fill="#3A8138" />
            <path d="M32.1876 28.3751L30 24L24 36H28.3751L32.1876 28.3751Z" fill="#3A8138" />
            <path d="M38.1876 28.3751L36 24L30 36H34.3751L38.1876 28.3751Z" fill="#3A8138" />
            <path d="M42 24L48 36H36L42 24Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-project-32" width="54" height="36" viewBox="0 0 54 36">
            <path d="M6 0L8.18756 4.37512L7.09317 6.5639L6 4.37756L2.18878 12H0L6 0Z" fill="#3A8138" />
            <path d="M14.1876 4.37512L12 0L7.90561 8.18878L9 10.3776L12 4.37756L13.0932 6.56391L14.1876 4.37512Z" fill="#3A8138" />
            <path d="M20.1876 4.37512L18 0L13.9056 8.18878L15 10.3776L18 4.37756L19.0932 6.56391L20.1876 4.37512Z" fill="#3A8138" />
            <path d="M26.1876 4.37512L24 0L19.9056 8.18878L21 10.3776L24 4.37756L25.0932 6.56391L26.1876 4.37512Z" fill="#3A8138" />
            <path d="M32.1876 4.37512L30 0L25.9056 8.18878L27 10.3776L30 4.37756L31.0932 6.56391L32.1876 4.37512Z" fill="#3A8138" />
            <path d="M38.1876 4.37512L36 0L31.9056 8.18878L33 10.3776L36 4.37756L37.0932 6.56391L38.1876 4.37512Z" fill="#3A8138" />
            <path d="M44.1876 4.37512L42 0L37.9056 8.18878L39 10.3776L42 4.37756L43.0932 6.56391L44.1876 4.37512Z" fill="#3A8138" />
            <path d="M54 12L48 0L43.9056 8.18878L45 10.3776L48 4.37756L51.8112 12H54Z" fill="#3A8138" />
            <path d="M6 8L8.18756 12.3751L7.09317 14.5639L6 12.3776L2.18878 20H0L6 8Z" fill="#3A8138" />
            <path d="M14.1876 12.3751L12 8L7.90561 16.1888L9 18.3776L12 12.3776L13.0932 14.5639L14.1876 12.3751Z" fill="#3A8138" />
            <path d="M20.1876 12.3751L18 8L13.9056 16.1888L15 18.3776L18 12.3776L19.0932 14.5639L20.1876 12.3751Z" fill="#3A8138" />
            <path d="M26.1876 12.3751L24 8L19.9056 16.1888L21 18.3776L24 12.3776L25.0932 14.5639L26.1876 12.3751Z" fill="#3A8138" />
            <path d="M32.1876 12.3751L30 8L25.9056 16.1888L27 18.3776L30 12.3776L31.0932 14.5639L32.1876 12.3751Z" fill="#3A8138" />
            <path d="M38.1876 12.3751L36 8L31.9056 16.1888L33 18.3776L36 12.3776L37.0932 14.5639L38.1876 12.3751Z" fill="#3A8138" />
            <path d="M44.1876 12.3751L42 8L37.9056 16.1888L39 18.3776L42 12.3776L43.0932 14.5639L44.1876 12.3751Z" fill="#3A8138" />
            <path d="M54 20L48 8L43.9056 16.1888L45 18.3776L48 12.3776L51.8112 20H54Z" fill="#3A8138" />
            <path d="M6 16L8.18756 20.3751L7.09317 22.5639L6 20.3776L2.18878 28H0L6 16Z" fill="#3A8138" />
            <path d="M14.1876 20.3751L12 16L7.90561 24.1888L9 26.3776L12 20.3776L13.0932 22.5639L14.1876 20.3751Z" fill="#3A8138" />
            <path d="M20.1876 20.3751L18 16L13.9056 24.1888L15 26.3776L18 20.3776L19.0932 22.5639L20.1876 20.3751Z" fill="#3A8138" />
            <path d="M26.1876 20.3751L24 16L19.9056 24.1888L21 26.3776L24 20.3776L25.0932 22.5639L26.1876 20.3751Z" fill="#3A8138" />
            <path d="M32.1876 20.3751L30 16L25.9056 24.1888L27 26.3776L30 20.3776L31.0932 22.5639L32.1876 20.3751Z" fill="#3A8138" />
            <path d="M38.1876 20.3751L36 16L31.9056 24.1888L33 26.3776L36 20.3776L37.0932 22.5639L38.1876 20.3751Z" fill="#3A8138" />
            <path d="M44.1876 20.3751L42 16L37.9056 24.1888L39 26.3776L42 20.3776L43.0932 22.5639L44.1876 20.3751Z" fill="#3A8138" />
            <path d="M54 28L48 16L43.9056 24.1888L45 26.3776L48 20.3776L51.8112 28H54Z" fill="#3A8138" />
            <path d="M8.18756 28.3751L6 24L0 36H4.37512L8.18756 28.3751Z" fill="#3A8138" />
            <path d="M14.1876 28.3751L12 24L6 36H10.3751L14.1876 28.3751Z" fill="#3A8138" />
            <path d="M20.1876 28.3751L18 24L12 36H16.3751L20.1876 28.3751Z" fill="#3A8138" />
            <path d="M26.1876 28.3751L24 24L18 36H22.3751L26.1876 28.3751Z" fill="#3A8138" />
            <path d="M32.1876 28.3751L30 24L24 36H28.3751L32.1876 28.3751Z" fill="#3A8138" />
            <path d="M38.1876 28.3751L36 24L30 36H34.3751L38.1876 28.3751Z" fill="#3A8138" />
            <path d="M44.1876 28.3751L42 24L36 36H40.3751L44.1876 28.3751Z" fill="#3A8138" />
            <path d="M48 24L54 36H42L48 24Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-project-33" width="54" height="44" viewBox="0 0 54 44">
            <path d="M6 0L8.18756 4.37512L7.09317 6.5639L6 4.37756L2.18878 12H0L6 0Z" fill="#3A8138" />
            <path d="M14.1876 4.37512L12 0L7.90561 8.18878L9 10.3776L12 4.37756L13.0932 6.56391L14.1876 4.37512Z" fill="#3A8138" />
            <path d="M20.1876 4.37512L18 0L13.9056 8.18878L15 10.3776L18 4.37756L19.0932 6.56391L20.1876 4.37512Z" fill="#3A8138" />
            <path d="M26.1876 4.37512L24 0L19.9056 8.18878L21 10.3776L24 4.37756L25.0932 6.56391L26.1876 4.37512Z" fill="#3A8138" />
            <path d="M32.1876 4.37512L30 0L25.9056 8.18878L27 10.3776L30 4.37756L31.0932 6.56391L32.1876 4.37512Z" fill="#3A8138" />
            <path d="M38.1876 4.37512L36 0L31.9056 8.18878L33 10.3776L36 4.37756L37.0932 6.56391L38.1876 4.37512Z" fill="#3A8138" />
            <path d="M44.1876 4.37512L42 0L37.9056 8.18878L39 10.3776L42 4.37756L43.0932 6.56391L44.1876 4.37512Z" fill="#3A8138" />
            <path d="M54 12L48 0L43.9056 8.18878L45 10.3776L48 4.37756L51.8112 12H54Z" fill="#3A8138" />
            <path d="M6 8L8.18756 12.3751L7.09317 14.5639L6 12.3776L2.18878 20H0L6 8Z" fill="#3A8138" />
            <path d="M14.1876 12.3751L12 8L7.90561 16.1888L9 18.3776L12 12.3776L13.0932 14.5639L14.1876 12.3751Z" fill="#3A8138" />
            <path d="M20.1876 12.3751L18 8L13.9056 16.1888L15 18.3776L18 12.3776L19.0932 14.5639L20.1876 12.3751Z" fill="#3A8138" />
            <path d="M26.1876 12.3751L24 8L19.9056 16.1888L21 18.3776L24 12.3776L25.0932 14.5639L26.1876 12.3751Z" fill="#3A8138" />
            <path d="M32.1876 12.3751L30 8L25.9056 16.1888L27 18.3776L30 12.3776L31.0932 14.5639L32.1876 12.3751Z" fill="#3A8138" />
            <path d="M38.1876 12.3751L36 8L31.9056 16.1888L33 18.3776L36 12.3776L37.0932 14.5639L38.1876 12.3751Z" fill="#3A8138" />
            <path d="M44.1876 12.3751L42 8L37.9056 16.1888L39 18.3776L42 12.3776L43.0932 14.5639L44.1876 12.3751Z" fill="#3A8138" />
            <path d="M54 20L48 8L43.9056 16.1888L45 18.3776L48 12.3776L51.8112 20H54Z" fill="#3A8138" />
            <path d="M6 16L8.18756 20.3751L7.09317 22.5639L6 20.3776L2.18878 28H0L6 16Z" fill="#3A8138" />
            <path d="M14.1876 20.3751L12 16L7.90561 24.1888L9 26.3776L12 20.3776L13.0932 22.5639L14.1876 20.3751Z" fill="#3A8138" />
            <path d="M20.1876 20.3751L18 16L13.9056 24.1888L15 26.3776L18 20.3776L19.0932 22.5639L20.1876 20.3751Z" fill="#3A8138" />
            <path d="M26.1876 20.3751L24 16L19.9056 24.1888L21 26.3776L24 20.3776L25.0932 22.5639L26.1876 20.3751Z" fill="#3A8138" />
            <path d="M32.1876 20.3751L30 16L25.9056 24.1888L27 26.3776L30 20.3776L31.0932 22.5639L32.1876 20.3751Z" fill="#3A8138" />
            <path d="M38.1876 20.3751L36 16L31.9056 24.1888L33 26.3776L36 20.3776L37.0932 22.5639L38.1876 20.3751Z" fill="#3A8138" />
            <path d="M44.1876 20.3751L42 16L37.9056 24.1888L39 26.3776L42 20.3776L43.0932 22.5639L44.1876 20.3751Z" fill="#3A8138" />
            <path d="M54 28L48 16L43.9056 24.1888L45 26.3776L48 20.3776L51.8112 28H54Z" fill="#3A8138" />
            <path d="M6 24L8.18756 28.3751L7.09317 30.5639L6 28.3776L2.18878 36H0L6 24Z" fill="#3A8138" />
            <path d="M12 24L14.1876 28.3751L10.3751 36H9.81122L7.90561 32.1888L12 24Z" fill="#3A8138" />
            <path d="M20.1876 28.3751L18 24L12 36H16.3751L20.1876 28.3751Z" fill="#3A8138" />
            <path d="M26.1876 28.3751L24 24L18 36H22.3751L26.1876 28.3751Z" fill="#3A8138" />
            <path d="M32.1876 28.3751L30 24L24 36H28.3751L32.1876 28.3751Z" fill="#3A8138" />
            <path d="M38.1876 28.3751L36 24L30 36H34.3751L38.1876 28.3751Z" fill="#3A8138" />
            <path d="M44.1876 28.3751L42 24L36 36H40.3751L44.1876 28.3751Z" fill="#3A8138" />
            <path d="M48 24L54 36H42L48 24Z" fill="#3A8138" />
            <path d="M6 32L12 44H0L6 32Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-project-34" width="54" height="44" viewBox="0 0 54 44">
            <path d="M6 0L8.18756 4.37512L7.09317 6.5639L6 4.37756L2.18878 12H0L6 0Z" fill="#3A8138" />
            <path d="M14.1876 4.37512L12 0L7.90561 8.18878L9 10.3776L12 4.37756L13.0932 6.56391L14.1876 4.37512Z" fill="#3A8138" />
            <path d="M20.1876 4.37512L18 0L13.9056 8.18878L15 10.3776L18 4.37756L19.0932 6.56391L20.1876 4.37512Z" fill="#3A8138" />
            <path d="M26.1876 4.37512L24 0L19.9056 8.18878L21 10.3776L24 4.37756L25.0932 6.56391L26.1876 4.37512Z" fill="#3A8138" />
            <path d="M32.1876 4.37512L30 0L25.9056 8.18878L27 10.3776L30 4.37756L31.0932 6.56391L32.1876 4.37512Z" fill="#3A8138" />
            <path d="M38.1876 4.37512L36 0L31.9056 8.18878L33 10.3776L36 4.37756L37.0932 6.56391L38.1876 4.37512Z" fill="#3A8138" />
            <path d="M44.1876 4.37512L42 0L37.9056 8.18878L39 10.3776L42 4.37756L43.0932 6.56391L44.1876 4.37512Z" fill="#3A8138" />
            <path d="M54 12L48 0L43.9056 8.18878L45 10.3776L48 4.37756L51.8112 12H54Z" fill="#3A8138" />
            <path d="M6 8L8.18756 12.3751L7.09317 14.5639L6 12.3776L2.18878 20H0L6 8Z" fill="#3A8138" />
            <path d="M14.1876 12.3751L12 8L7.90561 16.1888L9 18.3776L12 12.3776L13.0932 14.5639L14.1876 12.3751Z" fill="#3A8138" />
            <path d="M20.1876 12.3751L18 8L13.9056 16.1888L15 18.3776L18 12.3776L19.0932 14.5639L20.1876 12.3751Z" fill="#3A8138" />
            <path d="M26.1876 12.3751L24 8L19.9056 16.1888L21 18.3776L24 12.3776L25.0932 14.5639L26.1876 12.3751Z" fill="#3A8138" />
            <path d="M32.1876 12.3751L30 8L25.9056 16.1888L27 18.3776L30 12.3776L31.0932 14.5639L32.1876 12.3751Z" fill="#3A8138" />
            <path d="M38.1876 12.3751L36 8L31.9056 16.1888L33 18.3776L36 12.3776L37.0932 14.5639L38.1876 12.3751Z" fill="#3A8138" />
            <path d="M44.1876 12.3751L42 8L37.9056 16.1888L39 18.3776L42 12.3776L43.0932 14.5639L44.1876 12.3751Z" fill="#3A8138" />
            <path d="M54 20L48 8L43.9056 16.1888L45 18.3776L48 12.3776L51.8112 20H54Z" fill="#3A8138" />
            <path d="M6 16L8.18756 20.3751L7.09317 22.5639L6 20.3776L2.18878 28H0L6 16Z" fill="#3A8138" />
            <path d="M14.1876 20.3751L12 16L7.90561 24.1888L9 26.3776L12 20.3776L13.0932 22.5639L14.1876 20.3751Z" fill="#3A8138" />
            <path d="M20.1876 20.3751L18 16L13.9056 24.1888L15 26.3776L18 20.3776L19.0932 22.5639L20.1876 20.3751Z" fill="#3A8138" />
            <path d="M26.1876 20.3751L24 16L19.9056 24.1888L21 26.3776L24 20.3776L25.0932 22.5639L26.1876 20.3751Z" fill="#3A8138" />
            <path d="M32.1876 20.3751L30 16L25.9056 24.1888L27 26.3776L30 20.3776L31.0932 22.5639L32.1876 20.3751Z" fill="#3A8138" />
            <path d="M38.1876 20.3751L36 16L31.9056 24.1888L33 26.3776L36 20.3776L37.0932 22.5639L38.1876 20.3751Z" fill="#3A8138" />
            <path d="M44.1876 20.3751L42 16L37.9056 24.1888L39 26.3776L42 20.3776L43.0932 22.5639L44.1876 20.3751Z" fill="#3A8138" />
            <path d="M54 28L48 16L43.9056 24.1888L45 26.3776L48 20.3776L51.8112 28H54Z" fill="#3A8138" />
            <path d="M6 24L8.18756 28.3751L7.09317 30.5639L6 28.3776L2.18878 36H0L6 24Z" fill="#3A8138" />
            <path d="M14.1876 28.3751L12 24L7.90561 32.1888L9 34.3776L12 28.3776L13.0932 30.5639L14.1876 28.3751Z" fill="#3A8138" />
            <path d="M18 24L20.1876 28.3751L16.3751 36H15.8112L13.9056 32.1888L18 24Z" fill="#3A8138" />
            <path d="M26.1876 28.3751L24 24L18 36H22.3751L26.1876 28.3751Z" fill="#3A8138" />
            <path d="M32.1876 28.3751L30 24L24 36H28.3751L32.1876 28.3751Z" fill="#3A8138" />
            <path d="M38.1876 28.3751L36 24L30 36H34.3751L38.1876 28.3751Z" fill="#3A8138" />
            <path d="M44.1876 28.3751L42 24L36 36H40.3751L44.1876 28.3751Z" fill="#3A8138" />
            <path d="M48 24L54 36H42L48 24Z" fill="#3A8138" />
            <path d="M8.18756 36.3751L6 32L0 44H4.37512L8.18756 36.3751Z" fill="#3A8138" />
            <path d="M12 32L18 44H6L12 32Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-project-35" width="54" height="44" viewBox="0 0 54 44">
            <path d="M6 0L8.18756 4.37512L7.09317 6.5639L6 4.37756L2.18878 12H0L6 0Z" fill="#3A8138" />
            <path d="M14.1876 4.37512L12 0L7.90561 8.18878L9 10.3776L12 4.37756L13.0932 6.56391L14.1876 4.37512Z" fill="#3A8138" />
            <path d="M20.1876 4.37512L18 0L13.9056 8.18878L15 10.3776L18 4.37756L19.0932 6.56391L20.1876 4.37512Z" fill="#3A8138" />
            <path d="M26.1876 4.37512L24 0L19.9056 8.18878L21 10.3776L24 4.37756L25.0932 6.56391L26.1876 4.37512Z" fill="#3A8138" />
            <path d="M32.1876 4.37512L30 0L25.9056 8.18878L27 10.3776L30 4.37756L31.0932 6.56391L32.1876 4.37512Z" fill="#3A8138" />
            <path d="M38.1876 4.37512L36 0L31.9056 8.18878L33 10.3776L36 4.37756L37.0932 6.56391L38.1876 4.37512Z" fill="#3A8138" />
            <path d="M44.1876 4.37512L42 0L37.9056 8.18878L39 10.3776L42 4.37756L43.0932 6.56391L44.1876 4.37512Z" fill="#3A8138" />
            <path d="M54 12L48 0L43.9056 8.18878L45 10.3776L48 4.37756L51.8112 12H54Z" fill="#3A8138" />
            <path d="M6 8L8.18756 12.3751L7.09317 14.5639L6 12.3776L2.18878 20H0L6 8Z" fill="#3A8138" />
            <path d="M14.1876 12.3751L12 8L7.90561 16.1888L9 18.3776L12 12.3776L13.0932 14.5639L14.1876 12.3751Z" fill="#3A8138" />
            <path d="M20.1876 12.3751L18 8L13.9056 16.1888L15 18.3776L18 12.3776L19.0932 14.5639L20.1876 12.3751Z" fill="#3A8138" />
            <path d="M26.1876 12.3751L24 8L19.9056 16.1888L21 18.3776L24 12.3776L25.0932 14.5639L26.1876 12.3751Z" fill="#3A8138" />
            <path d="M32.1876 12.3751L30 8L25.9056 16.1888L27 18.3776L30 12.3776L31.0932 14.5639L32.1876 12.3751Z" fill="#3A8138" />
            <path d="M38.1876 12.3751L36 8L31.9056 16.1888L33 18.3776L36 12.3776L37.0932 14.5639L38.1876 12.3751Z" fill="#3A8138" />
            <path d="M44.1876 12.3751L42 8L37.9056 16.1888L39 18.3776L42 12.3776L43.0932 14.5639L44.1876 12.3751Z" fill="#3A8138" />
            <path d="M54 20L48 8L43.9056 16.1888L45 18.3776L48 12.3776L51.8112 20H54Z" fill="#3A8138" />
            <path d="M6 16L8.18756 20.3751L7.09317 22.5639L6 20.3776L2.18878 28H0L6 16Z" fill="#3A8138" />
            <path d="M14.1876 20.3751L12 16L7.90561 24.1888L9 26.3776L12 20.3776L13.0932 22.5639L14.1876 20.3751Z" fill="#3A8138" />
            <path d="M20.1876 20.3751L18 16L13.9056 24.1888L15 26.3776L18 20.3776L19.0932 22.5639L20.1876 20.3751Z" fill="#3A8138" />
            <path d="M26.1876 20.3751L24 16L19.9056 24.1888L21 26.3776L24 20.3776L25.0932 22.5639L26.1876 20.3751Z" fill="#3A8138" />
            <path d="M32.1876 20.3751L30 16L25.9056 24.1888L27 26.3776L30 20.3776L31.0932 22.5639L32.1876 20.3751Z" fill="#3A8138" />
            <path d="M38.1876 20.3751L36 16L31.9056 24.1888L33 26.3776L36 20.3776L37.0932 22.5639L38.1876 20.3751Z" fill="#3A8138" />
            <path d="M44.1876 20.3751L42 16L37.9056 24.1888L39 26.3776L42 20.3776L43.0932 22.5639L44.1876 20.3751Z" fill="#3A8138" />
            <path d="M54 28L48 16L43.9056 24.1888L45 26.3776L48 20.3776L51.8112 28H54Z" fill="#3A8138" />
            <path d="M6 24L8.18756 28.3751L7.09317 30.5639L6 28.3776L2.18878 36H0L6 24Z" fill="#3A8138" />
            <path d="M14.1876 28.3751L12 24L7.90561 32.1888L9 34.3776L12 28.3776L13.0932 30.5639L14.1876 28.3751Z" fill="#3A8138" />
            <path d="M20.1876 28.3751L18 24L13.9056 32.1888L15 34.3776L18 28.3776L19.0932 30.5639L20.1876 28.3751Z" fill="#3A8138" />
            <path d="M24 24L26.1876 28.3751L22.3751 36H21.8112L19.9056 32.1888L24 24Z" fill="#3A8138" />
            <path d="M32.1876 28.3751L30 24L24 36H28.3751L32.1876 28.3751Z" fill="#3A8138" />
            <path d="M38.1876 28.3751L36 24L30 36H34.3751L38.1876 28.3751Z" fill="#3A8138" />
            <path d="M44.1876 28.3751L42 24L36 36H40.3751L44.1876 28.3751Z" fill="#3A8138" />
            <path d="M48 24L54 36H42L48 24Z" fill="#3A8138" />
            <path d="M8.18756 36.3751L6 32L0 44H4.37512L8.18756 36.3751Z" fill="#3A8138" />
            <path d="M14.1876 36.3751L12 32L6 44H10.3751L14.1876 36.3751Z" fill="#3A8138" />
            <path d="M18 32L24 44H12L18 32Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-project-36" width="54" height="44" viewBox="0 0 54 44">
            <path d="M6 0L8.18756 4.37512L7.09317 6.5639L6 4.37756L2.18878 12H0L6 0Z" fill="#3A8138" />
            <path d="M14.1876 4.37512L12 0L7.90561 8.18878L9 10.3776L12 4.37756L13.0932 6.56391L14.1876 4.37512Z" fill="#3A8138" />
            <path d="M20.1876 4.37512L18 0L13.9056 8.18878L15 10.3776L18 4.37756L19.0932 6.56391L20.1876 4.37512Z" fill="#3A8138" />
            <path d="M26.1876 4.37512L24 0L19.9056 8.18878L21 10.3776L24 4.37756L25.0932 6.56391L26.1876 4.37512Z" fill="#3A8138" />
            <path d="M32.1876 4.37512L30 0L25.9056 8.18878L27 10.3776L30 4.37756L31.0932 6.56391L32.1876 4.37512Z" fill="#3A8138" />
            <path d="M38.1876 4.37512L36 0L31.9056 8.18878L33 10.3776L36 4.37756L37.0932 6.56391L38.1876 4.37512Z" fill="#3A8138" />
            <path d="M44.1876 4.37512L42 0L37.9056 8.18878L39 10.3776L42 4.37756L43.0932 6.56391L44.1876 4.37512Z" fill="#3A8138" />
            <path d="M54 12L48 0L43.9056 8.18878L45 10.3776L48 4.37756L51.8112 12H54Z" fill="#3A8138" />
            <path d="M6 8L8.18756 12.3751L7.09317 14.5639L6 12.3776L2.18878 20H0L6 8Z" fill="#3A8138" />
            <path d="M14.1876 12.3751L12 8L7.90561 16.1888L9 18.3776L12 12.3776L13.0932 14.5639L14.1876 12.3751Z" fill="#3A8138" />
            <path d="M20.1876 12.3751L18 8L13.9056 16.1888L15 18.3776L18 12.3776L19.0932 14.5639L20.1876 12.3751Z" fill="#3A8138" />
            <path d="M26.1876 12.3751L24 8L19.9056 16.1888L21 18.3776L24 12.3776L25.0932 14.5639L26.1876 12.3751Z" fill="#3A8138" />
            <path d="M32.1876 12.3751L30 8L25.9056 16.1888L27 18.3776L30 12.3776L31.0932 14.5639L32.1876 12.3751Z" fill="#3A8138" />
            <path d="M38.1876 12.3751L36 8L31.9056 16.1888L33 18.3776L36 12.3776L37.0932 14.5639L38.1876 12.3751Z" fill="#3A8138" />
            <path d="M44.1876 12.3751L42 8L37.9056 16.1888L39 18.3776L42 12.3776L43.0932 14.5639L44.1876 12.3751Z" fill="#3A8138" />
            <path d="M54 20L48 8L43.9056 16.1888L45 18.3776L48 12.3776L51.8112 20H54Z" fill="#3A8138" />
            <path d="M6 16L8.18756 20.3751L7.09317 22.5639L6 20.3776L2.18878 28H0L6 16Z" fill="#3A8138" />
            <path d="M14.1876 20.3751L12 16L7.90561 24.1888L9 26.3776L12 20.3776L13.0932 22.5639L14.1876 20.3751Z" fill="#3A8138" />
            <path d="M20.1876 20.3751L18 16L13.9056 24.1888L15 26.3776L18 20.3776L19.0932 22.5639L20.1876 20.3751Z" fill="#3A8138" />
            <path d="M26.1876 20.3751L24 16L19.9056 24.1888L21 26.3776L24 20.3776L25.0932 22.5639L26.1876 20.3751Z" fill="#3A8138" />
            <path d="M32.1876 20.3751L30 16L25.9056 24.1888L27 26.3776L30 20.3776L31.0932 22.5639L32.1876 20.3751Z" fill="#3A8138" />
            <path d="M38.1876 20.3751L36 16L31.9056 24.1888L33 26.3776L36 20.3776L37.0932 22.5639L38.1876 20.3751Z" fill="#3A8138" />
            <path d="M44.1876 20.3751L42 16L37.9056 24.1888L39 26.3776L42 20.3776L43.0932 22.5639L44.1876 20.3751Z" fill="#3A8138" />
            <path d="M54 28L48 16L43.9056 24.1888L45 26.3776L48 20.3776L51.8112 28H54Z" fill="#3A8138" />
            <path d="M6 24L8.18756 28.3751L7.09317 30.5639L6 28.3776L2.18878 36H0L6 24Z" fill="#3A8138" />
            <path d="M14.1876 28.3751L12 24L7.90561 32.1888L9 34.3776L12 28.3776L13.0932 30.5639L14.1876 28.3751Z" fill="#3A8138" />
            <path d="M20.1876 28.3751L18 24L13.9056 32.1888L15 34.3776L18 28.3776L19.0932 30.5639L20.1876 28.3751Z" fill="#3A8138" />
            <path d="M26.1876 28.3751L24 24L19.9056 32.1888L21 34.3776L24 28.3776L25.0932 30.5639L26.1876 28.3751Z" fill="#3A8138" />
            <path d="M30 24L32.1876 28.3751L28.3751 36H27.8112L25.9056 32.1888L30 24Z" fill="#3A8138" />
            <path d="M36 24L38.1876 28.3751L34.3751 36H33.8112L31.9056 32.1888L36 24Z" fill="#3A8138" />
            <path d="M44.1876 28.3751L42 24L36 36H40.3751L44.1876 28.3751Z" fill="#3A8138" />
            <path d="M48 24L54 36H42L48 24Z" fill="#3A8138" />
            <path d="M8.18756 36.3751L6 32L0 44H4.37512L8.18756 36.3751Z" fill="#3A8138" />
            <path d="M14.1876 36.3751L12 32L6 44H10.3751L14.1876 36.3751Z" fill="#3A8138" />
            <path d="M20.1876 36.3751L18 32L12 44H16.3751L20.1876 36.3751Z" fill="#3A8138" />
            <path d="M24 32L30 44H18L24 32Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-project-37" width="54" height="44" viewBox="0 0 54 44">
            <path d="M6 0L8.18756 4.37512L7.09317 6.5639L6 4.37756L2.18878 12H0L6 0Z" fill="#3A8138" />
            <path d="M14.1876 4.37512L12 0L7.90561 8.18878L9 10.3776L12 4.37756L13.0932 6.56391L14.1876 4.37512Z" fill="#3A8138" />
            <path d="M20.1876 4.37512L18 0L13.9056 8.18878L15 10.3776L18 4.37756L19.0932 6.56391L20.1876 4.37512Z" fill="#3A8138" />
            <path d="M26.1876 4.37512L24 0L19.9056 8.18878L21 10.3776L24 4.37756L25.0932 6.56391L26.1876 4.37512Z" fill="#3A8138" />
            <path d="M32.1876 4.37512L30 0L25.9056 8.18878L27 10.3776L30 4.37756L31.0932 6.56391L32.1876 4.37512Z" fill="#3A8138" />
            <path d="M38.1876 4.37512L36 0L31.9056 8.18878L33 10.3776L36 4.37756L37.0932 6.56391L38.1876 4.37512Z" fill="#3A8138" />
            <path d="M44.1876 4.37512L42 0L37.9056 8.18878L39 10.3776L42 4.37756L43.0932 6.56391L44.1876 4.37512Z" fill="#3A8138" />
            <path d="M54 12L48 0L43.9056 8.18878L45 10.3776L48 4.37756L51.8112 12H54Z" fill="#3A8138" />
            <path d="M6 8L8.18756 12.3751L7.09317 14.5639L6 12.3776L2.18878 20H0L6 8Z" fill="#3A8138" />
            <path d="M14.1876 12.3751L12 8L7.90561 16.1888L9 18.3776L12 12.3776L13.0932 14.5639L14.1876 12.3751Z" fill="#3A8138" />
            <path d="M20.1876 12.3751L18 8L13.9056 16.1888L15 18.3776L18 12.3776L19.0932 14.5639L20.1876 12.3751Z" fill="#3A8138" />
            <path d="M26.1876 12.3751L24 8L19.9056 16.1888L21 18.3776L24 12.3776L25.0932 14.5639L26.1876 12.3751Z" fill="#3A8138" />
            <path d="M32.1876 12.3751L30 8L25.9056 16.1888L27 18.3776L30 12.3776L31.0932 14.5639L32.1876 12.3751Z" fill="#3A8138" />
            <path d="M38.1876 12.3751L36 8L31.9056 16.1888L33 18.3776L36 12.3776L37.0932 14.5639L38.1876 12.3751Z" fill="#3A8138" />
            <path d="M44.1876 12.3751L42 8L37.9056 16.1888L39 18.3776L42 12.3776L43.0932 14.5639L44.1876 12.3751Z" fill="#3A8138" />
            <path d="M54 20L48 8L43.9056 16.1888L45 18.3776L48 12.3776L51.8112 20H54Z" fill="#3A8138" />
            <path d="M6 16L8.18756 20.3751L7.09317 22.5639L6 20.3776L2.18878 28H0L6 16Z" fill="#3A8138" />
            <path d="M14.1876 20.3751L12 16L7.90561 24.1888L9 26.3776L12 20.3776L13.0932 22.5639L14.1876 20.3751Z" fill="#3A8138" />
            <path d="M20.1876 20.3751L18 16L13.9056 24.1888L15 26.3776L18 20.3776L19.0932 22.5639L20.1876 20.3751Z" fill="#3A8138" />
            <path d="M26.1876 20.3751L24 16L19.9056 24.1888L21 26.3776L24 20.3776L25.0932 22.5639L26.1876 20.3751Z" fill="#3A8138" />
            <path d="M32.1876 20.3751L30 16L25.9056 24.1888L27 26.3776L30 20.3776L31.0932 22.5639L32.1876 20.3751Z" fill="#3A8138" />
            <path d="M38.1876 20.3751L36 16L31.9056 24.1888L33 26.3776L36 20.3776L37.0932 22.5639L38.1876 20.3751Z" fill="#3A8138" />
            <path d="M44.1876 20.3751L42 16L37.9056 24.1888L39 26.3776L42 20.3776L43.0932 22.5639L44.1876 20.3751Z" fill="#3A8138" />
            <path d="M54 28L48 16L43.9056 24.1888L45 26.3776L48 20.3776L51.8112 28H54Z" fill="#3A8138" />
            <path d="M6 24L8.18756 28.3751L7.09317 30.5639L6 28.3776L2.18878 36H0L6 24Z" fill="#3A8138" />
            <path d="M14.1876 28.3751L12 24L7.90561 32.1888L9 34.3776L12 28.3776L13.0932 30.5639L14.1876 28.3751Z" fill="#3A8138" />
            <path d="M20.1876 28.3751L18 24L13.9056 32.1888L15 34.3776L18 28.3776L19.0932 30.5639L20.1876 28.3751Z" fill="#3A8138" />
            <path d="M26.1876 28.3751L24 24L19.9056 32.1888L21 34.3776L24 28.3776L25.0932 30.5639L26.1876 28.3751Z" fill="#3A8138" />
            <path d="M32.1876 28.3751L30 24L25.9056 32.1888L27 34.3776L30 28.3776L31.0932 30.5639L32.1876 28.3751Z" fill="#3A8138" />
            <path d="M36 24L38.1876 28.3751L34.3751 36H33.8112L31.9056 32.1888L36 24Z" fill="#3A8138" />
            <path d="M44.1876 28.3751L42 24L36 36H40.3751L44.1876 28.3751Z" fill="#3A8138" />
            <path d="M48 24L54 36H42L48 24Z" fill="#3A8138" />
            <path d="M8.18756 36.3751L6 32L0 44H4.37512L8.18756 36.3751Z" fill="#3A8138" />
            <path d="M14.1876 36.3751L12 32L6 44H10.3751L14.1876 36.3751Z" fill="#3A8138" />
            <path d="M20.1876 36.3751L18 32L12 44H16.3751L20.1876 36.3751Z" fill="#3A8138" />
            <path d="M26.1876 36.3751L24 32L18 44H22.3751L26.1876 36.3751Z" fill="#3A8138" />
            <path d="M30 32L36 44H24L30 32Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-project-38" width="54" height="44" viewBox="0 0 54 44">
            <path d="M6 0L8.18756 4.37512L7.09317 6.5639L6 4.37756L2.18878 12H0L6 0Z" fill="#3A8138" />
            <path d="M14.1876 4.37512L12 0L7.90561 8.18878L9 10.3776L12 4.37756L13.0932 6.56391L14.1876 4.37512Z" fill="#3A8138" />
            <path d="M20.1876 4.37512L18 0L13.9056 8.18878L15 10.3776L18 4.37756L19.0932 6.56391L20.1876 4.37512Z" fill="#3A8138" />
            <path d="M26.1876 4.37512L24 0L19.9056 8.18878L21 10.3776L24 4.37756L25.0932 6.56391L26.1876 4.37512Z" fill="#3A8138" />
            <path d="M32.1876 4.37512L30 0L25.9056 8.18878L27 10.3776L30 4.37756L31.0932 6.56391L32.1876 4.37512Z" fill="#3A8138" />
            <path d="M38.1876 4.37512L36 0L31.9056 8.18878L33 10.3776L36 4.37756L37.0932 6.56391L38.1876 4.37512Z" fill="#3A8138" />
            <path d="M44.1876 4.37512L42 0L37.9056 8.18878L39 10.3776L42 4.37756L43.0932 6.56391L44.1876 4.37512Z" fill="#3A8138" />
            <path d="M54 12L48 0L43.9056 8.18878L45 10.3776L48 4.37756L51.8112 12H54Z" fill="#3A8138" />
            <path d="M6 8L8.18756 12.3751L7.09317 14.5639L6 12.3776L2.18878 20H0L6 8Z" fill="#3A8138" />
            <path d="M14.1876 12.3751L12 8L7.90561 16.1888L9 18.3776L12 12.3776L13.0932 14.5639L14.1876 12.3751Z" fill="#3A8138" />
            <path d="M20.1876 12.3751L18 8L13.9056 16.1888L15 18.3776L18 12.3776L19.0932 14.5639L20.1876 12.3751Z" fill="#3A8138" />
            <path d="M26.1876 12.3751L24 8L19.9056 16.1888L21 18.3776L24 12.3776L25.0932 14.5639L26.1876 12.3751Z" fill="#3A8138" />
            <path d="M32.1876 12.3751L30 8L25.9056 16.1888L27 18.3776L30 12.3776L31.0932 14.5639L32.1876 12.3751Z" fill="#3A8138" />
            <path d="M38.1876 12.3751L36 8L31.9056 16.1888L33 18.3776L36 12.3776L37.0932 14.5639L38.1876 12.3751Z" fill="#3A8138" />
            <path d="M44.1876 12.3751L42 8L37.9056 16.1888L39 18.3776L42 12.3776L43.0932 14.5639L44.1876 12.3751Z" fill="#3A8138" />
            <path d="M54 20L48 8L43.9056 16.1888L45 18.3776L48 12.3776L51.8112 20H54Z" fill="#3A8138" />
            <path d="M6 16L8.18756 20.3751L7.09317 22.5639L6 20.3776L2.18878 28H0L6 16Z" fill="#3A8138" />
            <path d="M14.1876 20.3751L12 16L7.90561 24.1888L9 26.3776L12 20.3776L13.0932 22.5639L14.1876 20.3751Z" fill="#3A8138" />
            <path d="M20.1876 20.3751L18 16L13.9056 24.1888L15 26.3776L18 20.3776L19.0932 22.5639L20.1876 20.3751Z" fill="#3A8138" />
            <path d="M26.1876 20.3751L24 16L19.9056 24.1888L21 26.3776L24 20.3776L25.0932 22.5639L26.1876 20.3751Z" fill="#3A8138" />
            <path d="M32.1876 20.3751L30 16L25.9056 24.1888L27 26.3776L30 20.3776L31.0932 22.5639L32.1876 20.3751Z" fill="#3A8138" />
            <path d="M38.1876 20.3751L36 16L31.9056 24.1888L33 26.3776L36 20.3776L37.0932 22.5639L38.1876 20.3751Z" fill="#3A8138" />
            <path d="M44.1876 20.3751L42 16L37.9056 24.1888L39 26.3776L42 20.3776L43.0932 22.5639L44.1876 20.3751Z" fill="#3A8138" />
            <path d="M54 28L48 16L43.9056 24.1888L45 26.3776L48 20.3776L51.8112 28H54Z" fill="#3A8138" />
            <path d="M6 24L8.18756 28.3751L7.09317 30.5639L6 28.3776L2.18878 36H0L6 24Z" fill="#3A8138" />
            <path d="M14.1876 28.3751L12 24L7.90561 32.1888L9 34.3776L12 28.3776L13.0932 30.5639L14.1876 28.3751Z" fill="#3A8138" />
            <path d="M20.1876 28.3751L18 24L13.9056 32.1888L15 34.3776L18 28.3776L19.0932 30.5639L20.1876 28.3751Z" fill="#3A8138" />
            <path d="M26.1876 28.3751L24 24L19.9056 32.1888L21 34.3776L24 28.3776L25.0932 30.5639L26.1876 28.3751Z" fill="#3A8138" />
            <path d="M32.1876 28.3751L30 24L25.9056 32.1888L27 34.3776L30 28.3776L31.0932 30.5639L32.1876 28.3751Z" fill="#3A8138" />
            <path d="M38.1876 28.3751L36 24L31.9056 32.1888L33 34.3776L36 28.3776L37.0932 30.5639L38.1876 28.3751Z" fill="#3A8138" />
            <path d="M42 24L44.1876 28.3751L40.3751 36H39.8112L37.9056 32.1888L42 24Z" fill="#3A8138" />
            <path d="M48 24L54 36H42L48 24Z" fill="#3A8138" />
            <path d="M8.18756 36.3751L6 32L0 44H4.37512L8.18756 36.3751Z" fill="#3A8138" />
            <path d="M14.1876 36.3751L12 32L6 44H10.3751L14.1876 36.3751Z" fill="#3A8138" />
            <path d="M20.1876 36.3751L18 32L12 44H16.3751L20.1876 36.3751Z" fill="#3A8138" />
            <path d="M26.1876 36.3751L24 32L18 44H22.3751L26.1876 36.3751Z" fill="#3A8138" />
            <path d="M32.1876 36.3751L30 32L24 44H28.3751L32.1876 36.3751Z" fill="#3A8138" />
            <path d="M36 32L42 44H30L36 32Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-project-39" width="54" height="44" viewBox="0 0 54 44">
            <path d="M6 0L8.18756 4.37512L7.09317 6.5639L6 4.37756L2.18878 12H0L6 0Z" fill="#3A8138" />
            <path d="M14.1876 4.37512L12 0L7.90561 8.18878L9 10.3776L12 4.37756L13.0932 6.56391L14.1876 4.37512Z" fill="#3A8138" />
            <path d="M20.1876 4.37512L18 0L13.9056 8.18878L15 10.3776L18 4.37756L19.0932 6.56391L20.1876 4.37512Z" fill="#3A8138" />
            <path d="M26.1876 4.37512L24 0L19.9056 8.18878L21 10.3776L24 4.37756L25.0932 6.56391L26.1876 4.37512Z" fill="#3A8138" />
            <path d="M32.1876 4.37512L30 0L25.9056 8.18878L27 10.3776L30 4.37756L31.0932 6.56391L32.1876 4.37512Z" fill="#3A8138" />
            <path d="M38.1876 4.37512L36 0L31.9056 8.18878L33 10.3776L36 4.37756L37.0932 6.56391L38.1876 4.37512Z" fill="#3A8138" />
            <path d="M44.1876 4.37512L42 0L37.9056 8.18878L39 10.3776L42 4.37756L43.0932 6.56391L44.1876 4.37512Z" fill="#3A8138" />
            <path d="M54 12L48 0L43.9056 8.18878L45 10.3776L48 4.37756L51.8112 12H54Z" fill="#3A8138" />
            <path d="M6 8L8.18756 12.3751L7.09317 14.5639L6 12.3776L2.18878 20H0L6 8Z" fill="#3A8138" />
            <path d="M14.1876 12.3751L12 8L7.90561 16.1888L9 18.3776L12 12.3776L13.0932 14.5639L14.1876 12.3751Z" fill="#3A8138" />
            <path d="M20.1876 12.3751L18 8L13.9056 16.1888L15 18.3776L18 12.3776L19.0932 14.5639L20.1876 12.3751Z" fill="#3A8138" />
            <path d="M26.1876 12.3751L24 8L19.9056 16.1888L21 18.3776L24 12.3776L25.0932 14.5639L26.1876 12.3751Z" fill="#3A8138" />
            <path d="M32.1876 12.3751L30 8L25.9056 16.1888L27 18.3776L30 12.3776L31.0932 14.5639L32.1876 12.3751Z" fill="#3A8138" />
            <path d="M38.1876 12.3751L36 8L31.9056 16.1888L33 18.3776L36 12.3776L37.0932 14.5639L38.1876 12.3751Z" fill="#3A8138" />
            <path d="M44.1876 12.3751L42 8L37.9056 16.1888L39 18.3776L42 12.3776L43.0932 14.5639L44.1876 12.3751Z" fill="#3A8138" />
            <path d="M54 20L48 8L43.9056 16.1888L45 18.3776L48 12.3776L51.8112 20H54Z" fill="#3A8138" />
            <path d="M6 16L8.18756 20.3751L7.09317 22.5639L6 20.3776L2.18878 28H0L6 16Z" fill="#3A8138" />
            <path d="M14.1876 20.3751L12 16L7.90561 24.1888L9 26.3776L12 20.3776L13.0932 22.5639L14.1876 20.3751Z" fill="#3A8138" />
            <path d="M20.1876 20.3751L18 16L13.9056 24.1888L15 26.3776L18 20.3776L19.0932 22.5639L20.1876 20.3751Z" fill="#3A8138" />
            <path d="M26.1876 20.3751L24 16L19.9056 24.1888L21 26.3776L24 20.3776L25.0932 22.5639L26.1876 20.3751Z" fill="#3A8138" />
            <path d="M32.1876 20.3751L30 16L25.9056 24.1888L27 26.3776L30 20.3776L31.0932 22.5639L32.1876 20.3751Z" fill="#3A8138" />
            <path d="M38.1876 20.3751L36 16L31.9056 24.1888L33 26.3776L36 20.3776L37.0932 22.5639L38.1876 20.3751Z" fill="#3A8138" />
            <path d="M44.1876 20.3751L42 16L37.9056 24.1888L39 26.3776L42 20.3776L43.0932 22.5639L44.1876 20.3751Z" fill="#3A8138" />
            <path d="M54 28L48 16L43.9056 24.1888L45 26.3776L48 20.3776L51.8112 28H54Z" fill="#3A8138" />
            <path d="M6 24L8.18756 28.3751L7.09317 30.5639L6 28.3776L2.18878 36H0L6 24Z" fill="#3A8138" />
            <path d="M14.1876 28.3751L12 24L7.90561 32.1888L9 34.3776L12 28.3776L13.0932 30.5639L14.1876 28.3751Z" fill="#3A8138" />
            <path d="M20.1876 28.3751L18 24L13.9056 32.1888L15 34.3776L18 28.3776L19.0932 30.5639L20.1876 28.3751Z" fill="#3A8138" />
            <path d="M26.1876 28.3751L24 24L19.9056 32.1888L21 34.3776L24 28.3776L25.0932 30.5639L26.1876 28.3751Z" fill="#3A8138" />
            <path d="M32.1876 28.3751L30 24L25.9056 32.1888L27 34.3776L30 28.3776L31.0932 30.5639L32.1876 28.3751Z" fill="#3A8138" />
            <path d="M38.1876 28.3751L36 24L31.9056 32.1888L33 34.3776L36 28.3776L37.0932 30.5639L38.1876 28.3751Z" fill="#3A8138" />
            <path d="M44.1876 28.3751L42 24L37.9056 32.1888L39 34.3776L42 28.3776L43.0932 30.5639L44.1876 28.3751Z" fill="#3A8138" />
            <path d="M54 36L48 24L43.9056 32.1888L45.8112 36H54Z" fill="#3A8138" />
            <path d="M8.18756 36.3751L6 32L0 44H4.37512L8.18756 36.3751Z" fill="#3A8138" />
            <path d="M14.1876 36.3751L12 32L6 44H10.3751L14.1876 36.3751Z" fill="#3A8138" />
            <path d="M20.1876 36.3751L18 32L12 44H16.3751L20.1876 36.3751Z" fill="#3A8138" />
            <path d="M26.1876 36.3751L24 32L18 44H22.3751L26.1876 36.3751Z" fill="#3A8138" />
            <path d="M32.1876 36.3751L30 32L24 44H28.3751L32.1876 36.3751Z" fill="#3A8138" />
            <path d="M38.1876 36.3751L36 32L30 44H34.3751L38.1876 36.3751Z" fill="#3A8138" />
            <path d="M42 32L48 44H36L42 32Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-project-4" width="30" height="12" viewBox="0 0 30 12">
            <path d="M8.18756 4.37512L6 0L0 12H4.37512L8.18756 4.37512Z" fill="#3A8138" />
            <path d="M14.1876 4.37512L12 0L6 12H10.3751L14.1876 4.37512Z" fill="#3A8138" />
            <path d="M20.1876 4.37512L18 0L12 12H16.3751L20.1876 4.37512Z" fill="#3A8138" />
            <path d="M24 0L30 12H18L24 0Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-project-40" width="54" height="44" viewBox="0 0 54 44">
            <path d="M6 0L8.18756 4.37512L7.09317 6.5639L6 4.37756L2.18878 12H0L6 0Z" fill="#3A8138" />
            <path d="M14.1876 4.37512L12 0L7.90561 8.18878L9 10.3776L12 4.37756L13.0932 6.56391L14.1876 4.37512Z" fill="#3A8138" />
            <path d="M20.1876 4.37512L18 0L13.9056 8.18878L15 10.3776L18 4.37756L19.0932 6.56391L20.1876 4.37512Z" fill="#3A8138" />
            <path d="M26.1876 4.37512L24 0L19.9056 8.18878L21 10.3776L24 4.37756L25.0932 6.56391L26.1876 4.37512Z" fill="#3A8138" />
            <path d="M32.1876 4.37512L30 0L25.9056 8.18878L27 10.3776L30 4.37756L31.0932 6.56391L32.1876 4.37512Z" fill="#3A8138" />
            <path d="M38.1876 4.37512L36 0L31.9056 8.18878L33 10.3776L36 4.37756L37.0932 6.56391L38.1876 4.37512Z" fill="#3A8138" />
            <path d="M44.1876 4.37512L42 0L37.9056 8.18878L39 10.3776L42 4.37756L43.0932 6.56391L44.1876 4.37512Z" fill="#3A8138" />
            <path d="M54 12L48 0L43.9056 8.18878L45 10.3776L48 4.37756L51.8112 12H54Z" fill="#3A8138" />
            <path d="M6 8L8.18756 12.3751L7.09317 14.5639L6 12.3776L2.18878 20H0L6 8Z" fill="#3A8138" />
            <path d="M14.1876 12.3751L12 8L7.90561 16.1888L9 18.3776L12 12.3776L13.0932 14.5639L14.1876 12.3751Z" fill="#3A8138" />
            <path d="M20.1876 12.3751L18 8L13.9056 16.1888L15 18.3776L18 12.3776L19.0932 14.5639L20.1876 12.3751Z" fill="#3A8138" />
            <path d="M26.1876 12.3751L24 8L19.9056 16.1888L21 18.3776L24 12.3776L25.0932 14.5639L26.1876 12.3751Z" fill="#3A8138" />
            <path d="M32.1876 12.3751L30 8L25.9056 16.1888L27 18.3776L30 12.3776L31.0932 14.5639L32.1876 12.3751Z" fill="#3A8138" />
            <path d="M38.1876 12.3751L36 8L31.9056 16.1888L33 18.3776L36 12.3776L37.0932 14.5639L38.1876 12.3751Z" fill="#3A8138" />
            <path d="M44.1876 12.3751L42 8L37.9056 16.1888L39 18.3776L42 12.3776L43.0932 14.5639L44.1876 12.3751Z" fill="#3A8138" />
            <path d="M54 20L48 8L43.9056 16.1888L45 18.3776L48 12.3776L51.8112 20H54Z" fill="#3A8138" />
            <path d="M6 16L8.18756 20.3751L7.09317 22.5639L6 20.3776L2.18878 28H0L6 16Z" fill="#3A8138" />
            <path d="M14.1876 20.3751L12 16L7.90561 24.1888L9 26.3776L12 20.3776L13.0932 22.5639L14.1876 20.3751Z" fill="#3A8138" />
            <path d="M20.1876 20.3751L18 16L13.9056 24.1888L15 26.3776L18 20.3776L19.0932 22.5639L20.1876 20.3751Z" fill="#3A8138" />
            <path d="M26.1876 20.3751L24 16L19.9056 24.1888L21 26.3776L24 20.3776L25.0932 22.5639L26.1876 20.3751Z" fill="#3A8138" />
            <path d="M32.1876 20.3751L30 16L25.9056 24.1888L27 26.3776L30 20.3776L31.0932 22.5639L32.1876 20.3751Z" fill="#3A8138" />
            <path d="M38.1876 20.3751L36 16L31.9056 24.1888L33 26.3776L36 20.3776L37.0932 22.5639L38.1876 20.3751Z" fill="#3A8138" />
            <path d="M44.1876 20.3751L42 16L37.9056 24.1888L39 26.3776L42 20.3776L43.0932 22.5639L44.1876 20.3751Z" fill="#3A8138" />
            <path d="M54 28L48 16L43.9056 24.1888L45 26.3776L48 20.3776L51.8112 28H54Z" fill="#3A8138" />
            <path d="M6 24L8.18756 28.3751L7.09317 30.5639L6 28.3776L2.18878 36H0L6 24Z" fill="#3A8138" />
            <path d="M14.1876 28.3751L12 24L7.90561 32.1888L9 34.3776L12 28.3776L13.0932 30.5639L14.1876 28.3751Z" fill="#3A8138" />
            <path d="M20.1876 28.3751L18 24L13.9056 32.1888L15 34.3776L18 28.3776L19.0932 30.5639L20.1876 28.3751Z" fill="#3A8138" />
            <path d="M26.1876 28.3751L24 24L19.9056 32.1888L21 34.3776L24 28.3776L25.0932 30.5639L26.1876 28.3751Z" fill="#3A8138" />
            <path d="M32.1876 28.3751L30 24L25.9056 32.1888L27 34.3776L30 28.3776L31.0932 30.5639L32.1876 28.3751Z" fill="#3A8138" />
            <path d="M38.1876 28.3751L36 24L31.9056 32.1888L33 34.3776L36 28.3776L37.0932 30.5639L38.1876 28.3751Z" fill="#3A8138" />
            <path d="M44.1876 28.3751L42 24L37.9056 32.1888L39 34.3776L42 28.3776L43.0932 30.5639L44.1876 28.3751Z" fill="#3A8138" />
            <path d="M54 36L48 24L43.9056 32.1888L45 34.3776L48 28.3776L51.8112 36H54Z" fill="#3A8138" />
            <path d="M8.18756 36.3751L6 32L0 44H4.37512L8.18756 36.3751Z" fill="#3A8138" />
            <path d="M14.1876 36.3751L12 32L6 44H10.3751L14.1876 36.3751Z" fill="#3A8138" />
            <path d="M20.1876 36.3751L18 32L12 44H16.3751L20.1876 36.3751Z" fill="#3A8138" />
            <path d="M26.1876 36.3751L24 32L18 44H22.3751L26.1876 36.3751Z" fill="#3A8138" />
            <path d="M32.1876 36.3751L30 32L24 44H28.3751L32.1876 36.3751Z" fill="#3A8138" />
            <path d="M38.1876 36.3751L36 32L30 44H34.3751L38.1876 36.3751Z" fill="#3A8138" />
            <path d="M44.1876 36.3751L42 32L36 44H40.3751L44.1876 36.3751Z" fill="#3A8138" />
            <path d="M48 32L54 44H42L48 32Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-project-5" width="36" height="12" viewBox="0 0 36 12">
            <path d="M8.18756 4.37512L6 0L0 12H4.37512L8.18756 4.37512Z" fill="#3A8138" />
            <path d="M14.1876 4.37512L12 0L6 12H10.3751L14.1876 4.37512Z" fill="#3A8138" />
            <path d="M20.1876 4.37512L18 0L12 12H16.3751L20.1876 4.37512Z" fill="#3A8138" />
            <path d="M26.1876 4.37512L24 0L18 12H22.3751L26.1876 4.37512Z" fill="#3A8138" />
            <path d="M30 0L36 12H24L30 0Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-project-6" width="42" height="12" viewBox="0 0 42 12">
            <path d="M8.18756 4.37512L6 0L0 12H4.37512L8.18756 4.37512Z" fill="#3A8138" />
            <path d="M14.1876 4.37512L12 0L6 12H10.3751L14.1876 4.37512Z" fill="#3A8138" />
            <path d="M20.1876 4.37512L18 0L12 12H16.3751L20.1876 4.37512Z" fill="#3A8138" />
            <path d="M26.1876 4.37512L24 0L18 12H22.3751L26.1876 4.37512Z" fill="#3A8138" />
            <path d="M32.1876 4.37512L30 0L24 12H28.3751L32.1876 4.37512Z" fill="#3A8138" />
            <path d="M36 0L42 12H30L36 0Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-project-7" width="48" height="12" viewBox="0 0 48 12">
            <path d="M8.18756 4.37512L6 0L0 12H4.37512L8.18756 4.37512Z" fill="#3A8138" />
            <path d="M14.1876 4.37512L12 0L6 12H10.3751L14.1876 4.37512Z" fill="#3A8138" />
            <path d="M20.1876 4.37512L18 0L12 12H16.3751L20.1876 4.37512Z" fill="#3A8138" />
            <path d="M26.1876 4.37512L24 0L18 12H22.3751L26.1876 4.37512Z" fill="#3A8138" />
            <path d="M32.1876 4.37512L30 0L24 12H28.3751L32.1876 4.37512Z" fill="#3A8138" />
            <path d="M38.1876 4.37512L36 0L30 12H34.3751L38.1876 4.37512Z" fill="#3A8138" />
            <path d="M42 0L48 12H36L42 0Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-project-8" width="54" height="12" viewBox="0 0 54 12">
            <path d="M8.18756 4.37512L6 0L0 12H4.37512L8.18756 4.37512Z" fill="#3A8138" />
            <path d="M14.1876 4.37512L12 0L6 12H10.3751L14.1876 4.37512Z" fill="#3A8138" />
            <path d="M20.1876 4.37512L18 0L12 12H16.3751L20.1876 4.37512Z" fill="#3A8138" />
            <path d="M26.1876 4.37512L24 0L18 12H22.3751L26.1876 4.37512Z" fill="#3A8138" />
            <path d="M32.1876 4.37512L30 0L24 12H28.3751L32.1876 4.37512Z" fill="#3A8138" />
            <path d="M38.1876 4.37512L36 0L30 12H34.3751L38.1876 4.37512Z" fill="#3A8138" />
            <path d="M44.1876 4.37512L42 0L36 12H40.3751L44.1876 4.37512Z" fill="#3A8138" />
            <path d="M48 0L54 12H42L48 0Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-project-9" width="54" height="20" viewBox="0 0 54 20">
            <path d="M6 0L8.18756 4.37512L7.09317 6.5639L6 4.37756L2.18878 12H0L6 0Z" fill="#3A8138" />
            <path d="M12 0L14.1876 4.37512L10.3751 12H9.81122L7.90561 8.18878L12 0Z" fill="#3A8138" />
            <path d="M20.1876 4.37512L18 0L12 12H16.3751L20.1876 4.37512Z" fill="#3A8138" />
            <path d="M26.1876 4.37512L24 0L18 12H22.3751L26.1876 4.37512Z" fill="#3A8138" />
            <path d="M32.1876 4.37512L30 0L24 12H28.3751L32.1876 4.37512Z" fill="#3A8138" />
            <path d="M38.1876 4.37512L36 0L30 12H34.3751L38.1876 4.37512Z" fill="#3A8138" />
            <path d="M44.1876 4.37512L42 0L36 12H40.3751L44.1876 4.37512Z" fill="#3A8138" />
            <path d="M48 0L54 12H42L48 0Z" fill="#3A8138" />
            <path d="M6 8L12 20H0L6 8Z" fill="#3A8138" />
      </symbol>
      <symbol id="symbol-chevron-up-down-14" width="14" height="14" viewBox="0 0 14 14">
            <path d="M11.9605 3.90799L7 0.0498543L2.03954 3.90799L2.96046 5.09201L7 1.95015L11.0395 5.09201L11.9605 3.90799Z" />
            <path d="M2.03954 10.092L7 13.9501L11.9605 10.092L11.0395 8.90799L7 12.0499L2.96046 8.90799L2.03954 10.092Z" />
      </symbol>
      <symbol id="symbol-external-site-14" width="14" height="14" viewBox="0 0 14 14">
            <path d="M10.1893 2.75H7V1.25H12.75V7H11.25V3.81066L6.53033 8.53033L5.46967 7.46967L10.1893 2.75Z"/>
            <path d="M3 4.75C2.86193 4.75 2.75 4.86193 2.75 5V11C2.75 11.1381 2.86193 11.25 3 11.25H9C9.13807 11.25 9.25 11.1381 9.25 11V8H10.75V11C10.75 11.9665 9.9665 12.75 9 12.75H3C2.0335 12.75 1.25 11.9665 1.25 11V5C1.25 4.0335 2.0335 3.25 3 3.25H6V4.75H3Z" />
      </symbol>
</svg>

<?php if (have_rows('header')) :
      the_row();
      $title = get_sub_field('title');
      $description = get_sub_field('description');
      $image = get_sub_field('image');
?>
      <header id="hero" class="mb-42 lg:mb-68">
            <div class="w-full lg:grid lg:grid-cols-2 lg:mx-auto lg:max-w-content lg:gap-x-16">

                  <div class="w-full aspect-hero-sm overflow-hidden mb-42 md:axpect-hero-lg lg:aspect-none lg:w-full lg:h-full lg:mb-42 lg:col-start-2 lg:col-end-3 lg:row-start-1 lg:row-end-2 xl:mb-0 xl:aspect-hero-lg xl:h-unset">
                        <?php if ($image) : ?>
                              <?php echo get_slideshow_markup(array($image => $title)); ?>
                        <?php endif; ?>
                  </div>

                  <div class="max-w-text mx-auto lg:mt-135 lg:mx-0 lg:col-start-1 lg:col-end-2 lg:self-end lg:row-start-1 lg:row-end-2">

                        <h1 class="text-h1-small text-smoky-blue-600 mb-16 lg:text-h1-large lg:mb-26">
                              <?php echo $title; ?>
                        </h1>

                        <div class="text-text text-body flex flex-col gap-6 mb-26 lg:mb-42">
                              <?php echo $description; ?>
                        </div>

                        <ul class="flex flex-col gap-10 md:grid md:grid-cols-2 lg:gap-y-2 xl:grid-cols-3">
                              <?php
                                    $hero_links = array(
                                          'directory' => __('Directory', "rri-mapping-tool"),
                                          'regions' => __('Regions', "rri-mapping-tool"),
                                          'topics' => __('What We Do', "rri-mapping-tool"),
                                          'expertise' => __('How We Work', "rri-mapping-tool"),
                                          'partners' => __('Partners', "rri-mapping-tool"),
                                          'collaborators' => __('Collaborators', "rri-mapping-tool"),
                                          'fellows' => __('Fellows', "rri-mapping-tool"),
                                    );

                                    foreach ($hero_links as $hash => $label):
                              ?>
                              <li>
                                    <?php echo get_hero_link($hash, $label); ?>
                              </li>
                              <?php endforeach ; ?>
                        </ul>

                  </div>

            </div>
      </header>
<?php endif; ?>
<?php if (have_rows('introduction')) :
      the_row();
      $heading = get_sub_field('heading');
      $description = get_sub_field('description');
      $image = get_sub_field('image');
?>
      <section id="intro" class="relative z-30 pt-42 -mb-42 lg:pb-0 lg:pt-68 lg:-mb-68">

            <div class="max-w-content mx-auto relative z-10 lg:grid lg:grid-cols-2 lg:gap-x-26 lg:items-center lg:justify-items-center">

                  <!-- heading -->
                  <h2 class="text-left text-h2-small text-smoky-blue-600 mb-16 max-w-text lg:mb-42 lg:text-h1-large lg:col-start-1 lg:col-end-3 lg:row-start-1 lg:row-end-2 w-full lg:text-center"><?php echo $heading; ?></h2>

                  <!-- introduction -->
                  <div class="w-full mb-26 lg:mx-0 lg:mb-0 lg:self-start lg:justify-self-end lg:col-start-2 lg:col-end-3 lg:row-start-2 lg:row-end-3 lg:p-0">
                        <div class="text-text text-para flex flex-col gap-6 max-w-text"><?php echo $description; ?></div>
                  </div>

                  <!-- stats -->
                  <div class="flex flex-col gap-10 items-center mb-26 max-w-intro-stats self-center mx-auto md:grid md:grid-cols-2 md:max-w-none lg:w-full lg:mx-0 lg:mb-0 lg:col-start-1 lg:col-end-2 lg:row-start-2 lg:row-end-3 lg:pr-42 lg:gap-16 lg:justify-self-end">
                        <?php echo get_button_markup(array(
                              'href' => '#partners',
                              'classname' => 'w-full',
                              'label' => '<h3 class="flex items-center lg:flex-col lg:items-end lg:flex-1">
                              <span class="text-h2-large text-green-500 inline-block w-[5.875rem] mr-6 text-center lg:w-full lg:mr-0 lg:text-right font-medium">'.count($partners).'</span> <span class="text-stats-label-small text-smoky-blue-600 lg:w-full lg:text-right underline">'.__("partners", "rri-mapping-tool").'</span>
                              </h3>'
                        )); ?>
                        <?php
                              $collaborator_count = '150+';
                              echo get_button_markup(array(
                              'href' => '#collaborators',
                              'classname' => 'w-full',
                              'label' => '<h3 class="flex items-center lg:flex-col lg:items-end lg:flex-1">
                              <span class="text-h2-large text-green-500 inline-block w-[5.875rem] mr-6 text-center lg:w-full lg:mr-0 lg:text-right font-medium">'.$collaborator_count.'</span> <span class="text-stats-label-small text-smoky-blue-600 lg:w-full lg:text-right underline">'.__("collaborators", "rri-mapping-tool").'</span>
                              </h3>'
                        )); ?>
                        <?php echo get_button_markup(array(
                              'href' => '#collaborators',
                              'classname' => 'w-full',
                              'label' => '<h3 class="flex items-center lg:flex-col lg:items-end lg:flex-1">
                              <span class="text-h2-large text-green-500 inline-block w-[5.875rem] mr-6 text-center lg:w-full lg:mr-0 lg:text-right font-medium">'.count($fellows).'</span> <span class="text-stats-label-small text-smoky-blue-600 lg:w-full lg:text-right underline">'.__("fellows", "rri-mapping-tool").'</span>
                              </h3>'
                        )); ?>
                        <?php $countries_label = __("countries", "rri-mapping-tool"); ?>
                        <h3 class="w-full flex items-center lg:flex-col lg:items-end lg:flex-1">
                              <span class="text-h2-large text-green-500 inline-block w-[5.875rem] mr-6 text-center lg:w-full lg:mr-0 lg:text-right font-medium"><?php echo count($countries); ?></span> <span class="text-stats-label-small text-smoky-blue-600 lg:w-full lg:text-right"><?php echo $countries_label; ?></span>
                        </h3>
                  </div>

                  <!-- img -->
                  <div class="overflow-hidden w-full self-center mx-auto aspect-intro-sm rounded-xl bg-neutral-200 md:aspect-intro-lg lg:rounded-xxl lg:col-start-1 lg:col-end-3 lg:row-start-3 lg:row-end-4 lg:max-w-[65.25rem] lg:mt-42">
                        <?php echo get_slideshow_markup(array($image => $heading)); ?>
                  </div>

            </div>

      </section>
<?php endif; ?>
<section id="mapping-platform" class="w-full relative pb-10 lg:grid lg:pb-68">
      <div id="map__top-trigger"></div>
      <div id="map_container" class="absolute z-20 w-full h-touch-map max-h-touch-map overflow-hidden lg:inset-0 lg:z-0 lg:h-full lg:w-full lg:max-h-none">
            <script id="map__data" hidden aria-hidden="true" style="display: none;">
                  const mapData = <?php echo json_encode($map_data); ?>;
            </script>
            <div id="map" class="w-full h-full"></div>
      </div>
      <div id="map__content" class="w-full mx-auto gap-x-26 items-start relative z-10 lg:max-w-content lg:grid lg:grid-cols-12 lg:grid-rows-[auto_1fr] lg:col-start-1 lg:col-end-2 lg:row-start-1 lg:row-end-2 lg:pointer-events-none" style="position: relative">

            <div id="directory" style=" position: absolute; top: 0px; width: 100%; height: 100%; left: 0; outline: none; pointer-events: none; user-select: none;" role="presentation" aria-hidden="true" tabindex="-1"></div>
            <div id="regions" style=" position: absolute; top: 0px; width: 100%; height: 100%; left: 0; outline: none; pointer-events: none; user-select: none;" role="presentation" aria-hidden="true" tabindex="-1"></div>
            <div id="topics" style=" position: absolute; top: 0px; width: 100%; height: 100%; left: 0; outline: none; pointer-events: none; user-select: none;" role="presentation" aria-hidden="true" tabindex="-1"></div>
            <div id="expertise" style=" position: absolute; top: 0px; width: 100%; height: 100%; left: 0; outline: none; pointer-events: none; user-select: none;" role="presentation" aria-hidden="true" tabindex="-1"></div>
            <div id="fellows" style=" position: absolute; top: 0px; width: 100%; height: 100%; left: 0; outline: none; pointer-events: none; user-select: none;" role="presentation" aria-hidden="true" tabindex="-1"></div>

            <ul role="tablist" class="relative overflow-hidden overflow-x-auto flex gap-x-12 whitespace-nowrap max-w-screen z-20 pointer-events-auto pt-26 pb-10 px-10 mb-10 md:gap-x-26 md:mb-16 md:pt-26 lg:pb-42 lg:pt-110 lg:mb-0 lg:max-w-content lg:px-0 lg:col-start-1 lg:col-end-13 lg:row-start-1 lg:row-end-2 lg:justify-self-start lg:overflow-x-hidden">
                  <?php
                        $tabs = array(
                              'directory' => __("Directory", "rri-mapping-tool"),
                              'regions' => __("Regions", "rri-mapping-tool"),
                              'topics' => __("What We Do", "rri-mapping-tool"),
                              'expertise' => __("How We Work", "rri-mapping-tool"),
                              'fellows' => __("Fellows", "rri-mapping-tool"),
                        );
                        $index = 1;
                        foreach($tabs as $hash => $label) :
                  ?>
                  <li role="presentation">
                        <?php echo get_button_markup(array(
                              'href' => '#'.$hash,
                              'id' => get_tab_id($index),
                              'label' => '<h2 class="map-tab">'.$label.'</h2>',
                              'attrs' => array(
                                    'role' => 'tab',
                                    'aria-selected' => json_encode($index === 1),
                                    'aria-controls' => 'tab--'.$hash
                              ),
                        )); ?>
                  </li>
                  <?php
                        $index++;
                        endforeach;
                  ?>
            </ul>

            <section role="tabpanel" id="tab--directory" aria-labelledby="<?php echo get_tab_id(1); ?>" class="pointer-events-auto">
                  <script id="directory__json" hidden aira-hidden="true">
                        const directoryData = <?php echo json_encode(array_values(array_merge($partners, $projects, $fellows))); ?>
                  </script>
                  <?php echo get_back_button_markup('directory'); ?>

                  <div class="tab__slide">
                        <div class="tab__landing pt-10 lg:rounded-m lg:bg-white lg:pt-0 lg:px-10 lx:px-16 lg:grid lg:grid-rows-[auto_1fr] lg:h-full">
                              <form id="directory__form" class="pb-10 border-b-1 border-border w-full mb-16 lg:pt-10 xl:pt-16">
                                    <div class="relative fill-neutral-500 text-neutral-500 mb-10 w-full">
                                          <input type="search" placeholder="<?php echo __("Search", "rri-mapping-tool"); ?>" name="query" id="query" class="pl-30 py-6 bg-neutral-50 rounded-m hover:bg-neutral-100 placeholder-neutral-700 text-black w-full" />
                                          <button id="directory__search" class="absolute top-0 h-full grid place-items-center rounded-m left-0 hover:bg-green-100 aspect-square hover:fill-green-600" aria-label="<?php echo __("Search", "rri-mapping-tool"); ?>">
                                                <?php echo get_icon_markup('search', 14); ?>
                                          </button>
                                    </div>

                                    <div id="directory__filters" class="w-full">

                                          <?php echo get_search_filter_group(
                                                __('Regions', "rri-mapping-tool"),
                                                'regions',
                                                $regions
                                          ); ?>

                                          <?php echo get_search_filter_group(
                                                __('What We Do', "rri-mapping-tool"),
                                                'topics',
                                                $topics
                                          ); ?>

                                          <?php echo get_search_filter_group(
                                                __('How We Work', "rri-mapping-tool"),
                                                'expertise',
                                                $expertise
                                          ); ?>

                                    </div>
                              </form>

                              <ul id="directory__results" class="lg:pb-10 lg:h-full lg:overflow-hidden lg:overflow-y-auto xl:pb-16">
                                    <?php foreach ($directory_results as $letter => $items) : ?>
                                          <li class="directory__result-group">
                                                <div class="directory__result-group__letter"><?php echo $letter; ?></div>
                                                <ul class="directory__result-group__items">
                                                      <?php foreach ($items as $item) :
                                                            $item_type = $item['type'] === 'member' ? $item['member_type']['name'] : ($item['type'] === 'project' ? __('Project','rri-mapping-tool') : __('Fellow','rri-mapping-tool'));
                                                            $item_id = $item['item_id'];
                                                      ?>
                                                            <li class="directory__result" data-item-id="<?php echo $item_id; ?>">
                                                                  <?php echo get_anchor_markup(
                                                                        '?focus='.$item['type'].'&id='.$item['id'] . '#directory',
                                                                        $item['name']
                                                                  ); ?>
                                                                  <span class="directory__result__name"><?php echo $item['name']; ?></span>
                                                                  <span class="directory__result__type"><?php echo $item_type; ?></span>
                                                            </li>
                                                      <?php endforeach; ?>
                                                </ul>
                                          </li>
                                    <?php endforeach; ?>
                                    <h3 id="directory__no-results" hidden aria-hidden="true" class="py-10 text-neutral-600 text-h4">No results</h3>
                              </ul>
                        </div>

                        <div class="tab__content" hidden aria-hidden="true">
                              <?php echo get_member_focus_section('directory', NULL, $map_items); ?>
                              <?php echo get_project_focus_section('directory', $map_items); ?>
                              <?php echo get_fellow_focus_section('directory', $map_items); ?>
                        </div>
                  </div>
            </section>
            <?php
            $regions_description = '';
            $what_we_do_description = '';
            $how_we_work_description = '';
            $fellows_description = '';

            if (have_rows('regions')) {
                  the_row();
                  $regions_description = get_sub_field('description');
            }
            if (have_rows('what_we_do')) {
                  the_row();
                  $what_we_do_description = get_sub_field('description');
            }
            if (have_rows('how_we_work')) {
                  the_row();
                  $how_we_work_description = get_sub_field('description');
            }
            if (have_rows('fellows')) {
                  the_row();
                  $fellows_description = get_sub_field('description');
            }
            ?>
            <?php echo get_taxonomy_section('regions', 2, $regions_description, $regions, $map_items); ?>
            <?php echo get_taxonomy_section('topics', 3, $what_we_do_description, $topics, $map_items); ?>
            <?php echo get_taxonomy_section('expertise', 4, $how_we_work_description, $expertise, $map_items); ?>
            <?php echo get_fellows_section(5, $fellows_description, $fellows); ?>

            <div id="map_window" class="col-start-6 col-end-13 row-start-2 row-end-3 sticky top-68 select-none w-full h-full"></div>
      </div>
      <div id="map__bottom-trigger"></div>
</section>
<?php if (have_rows('partners')) :
      the_row();
      $heading = get_sub_field('heading');
      $description = get_sub_field('description');
?>
      <section id="partners" class="relative pt-68 w-full flex lg:pt-110 lg:max-w-content lg:mx-auto">
            <div class="w-full relative z-10 lg:grid lg:grid-cols-12 lg:gap-x-26">
                  <div id="partners__nav" class="mb-26 mx-auto max-w-content w-full lg:mx-0 lg:col-start-1 lg:col-end-5 lg:mb-0">
                        <div class="sticky top-42 lg:top-68">
                              <h2 class="text-h2-large text-green-700"><?php echo strip_tags($heading); ?></h2>
                              <div class="mt-10 flex flex-col gap-y-6 text-body text-black"><?php echo strip_tags($description); ?></div>
                        </div>
                  </div>

                  <ul class="tab__main partners__list flex flex-nowrap gap-4 w-full max-w-screen overflow-hidden overflow-x-auto pb-10 px-10 md:pb-0 md:grid md:grid-cols-3 md:gap-4 lg:gap-6 lg:col-start-6 lg:col-end-13 lg:self-start lg:p-0 xxl:grid-cols-4">
                        <?php foreach ($partners as $partner) :
                              $id = $partner['id'];
                              $name = $partner['name'];
                              $logo = $partner['logo'] ?? "";
                              $suffix = isset($partner['abbreviation']) && (strlen($partner['abbreviation']) > 0)
                                    ? ' (' . $partner['abbreviation'] . ')'
                                    : '';
                        ?>
                              <li class="partner-card">
                                    <?php echo get_anchor_markup('?focus=member&id='.$id.'#directory',$name); ?>
                                    <div class="media-contain partner-card__logo">
                                          <img src="<?php echo $logo; ?>" alt="<?php echo $name; ?>" />
                                    </div>
                                    <h3 class="partner-card__name"><?php echo $name; ?><?php echo $suffix; ?></h3>
                              </li>
                        <?php endforeach; ?>
                  </ul>
            </div>
      </section>
<?php endif; ?>
<?php if (have_rows('collaborators')) :
      the_row();
      $heading = get_sub_field('heading');
      $description = get_sub_field('description');
      $letters = array_filter(array_unique(array_map(function ($collaborator) {
            return strtoupper(substr(trim($collaborator['name']), 0, 1));
      }, $collaborators)), function ($letter) {
            return strlen(trim($letter)) > 0;
      });
      sort($letters);
      $collaborators_by_letter = array();

      foreach ($collaborators as $collaborator) {
            $letter = strtoupper(substr($collaborator['name'], 0, 1));
            if (false !== strpos($collaborator['name'], 'http')) continue;
            if (!strlen(trim($letter))) continue;
            if (!isset($collaborators_by_letter[$letter])) {
                  $collaborators_by_letter[$letter] = array();
            }
            $collaborators_by_letter[$letter][] = $collaborator;
      }
?>
      <section id="collaborators" class="max-w-content w-full mx-auto py-42 lg:grid lg:grid-cols-12 lg:gap-x-26 lg:py-110">
            <div id="collaborators__nav" class="mb-26 lg:col-start-1 lg:col-end-5">
                  <div class="lg:sticky lg:top-68">
                        <h2 class="text-green-700 text-h2-small lg:text-h2-large"><?php echo $heading; ?></h2>
                        <div class="mt-10 flex flex-col gap-y-6 text-body text-black">
                              <?php echo strip_tags($description); ?>
                        </div>
                        <ul class="mt-26 text-green-600 flex flex-wrap uppercase gap-6 justify-center lg:justify-start lg:gap-4 collaborators__jump-links">
                              <?php foreach ($letters as $letter) : ?>
                                    <li class="collaborators__jump-link">
                                          <?php echo get_button_markup(array(
                                                'href' => '#collaborators--'.$letter,
                                                'label' => $letter
                                          )); ?>
                                    </li>
                              <?php endforeach; ?>
                        </ul>
                  </div>
            </div>
            <ul class="collaborators w-full lg:col-start-6 lg:col-end-13">
                  <?php foreach ($collaborators_by_letter as $letter => $collaborators) : ?>
                        <li class="collaborators__group">
                              <a id="collaborators--<?php echo $letter; ?>" class="anchor"></a>
                              <h3 class="collaborators__group__letter"><?php echo $letter; ?></h3>
                              <ul class="collaborators__group__list">
                                    <?php foreach ($collaborators as $collaborator) :
                                          $name = $collaborator['name'];
                                          $url = $collaborator['url'];
                                          $has_url = !is_null($url) && (strlen($url) > 0);
                                    ?>
                                    <li class="collaborator">
                                          <?php if ($has_url) : ?>
                                          <?php echo get_button_markup(array(
                                                'href' => $url,
                                                'target' => '_blank',
                                                'label' => $name
                                          )); ?>
                                          <?php else : ?>
                                          <?php echo $name; ?>
                                          <?php endif; ?>
                                    </li>
                                    <?php endforeach; ?>
                              </ul>
                        </li>
                  <?php endforeach; ?>
            </ul>
      </section>
<?php endif; ?>
<?php if (have_rows('blog_cta')) :
      the_row();
      $heading = get_sub_field('heading');
      $tagline = get_sub_field('tagline');
      $button = get_sub_field('button');
      $bg_image = get_sub_field('bg_image');
      $blog_articles = get_latest_blog_articles();
?>
      <section id="cta" class="overflow-hidden relative w-full bg-green-50">
            <div class="max-w-content w-full mx-auto py-42 lg:py-68">

                  <h2 class="text-h5 text-smoky-blue-700 mb-26 uppercase lg:row-start-1 lg:row-end-2 lg:col-start-1 lg:col-end-5"><?php echo $heading; ?></h2>

                  <ul class="flex flex-col gap-26 md:grid md:grid-cols-2 md:gap-10 lg:grid-cols-4">
                        <?php foreach ($blog_articles as $index => $article) :
                              $is_first = $index === 0;
                              $title = $article->post_title;
                              $excerpt_1 = get_field('field_5dd3fc8ffaee5', $article->ID);
                              $excerpt_2 = get_field('field_588f67cda3171', $article->ID);
                              $excerpt = strlen($excerpt_1) ? $excerpt_1 : $excerpt_2;
                              $date = date_create($article->post_date);
                              $formatted_date = date_format($date, 'd.m.Y');
                              $permalink = get_post_permalink($article->ID);
                              $col_start = $is_first ? 1 : $index;
                              $col_end = $is_first ? 5 : $col_start + 1;
                              $row_start = $is_first ? 1 : 2;
                              $row_end = $row_start + 1;
                              $thumbnail = Rri_Mapping_Tool_Utils::get_post_thumbnail($article->ID);
                        ?>
                        <li class="lg:col-start-<?php echo $col_start; ?> lg:col-end-<?php echo $col_end; ?> lg:row-start-<?php echo $row_start; ?> lg:row-end-<?php echo $row_end; ?> blog-card">
                              <div class="blog-card__thumb">
                                    <img src="<?php echo $thumbnail; ?>" alt="<?php echo $title; ?>" class="media-cover">
                              </div>
                              <div class="blog-card__content">
                                    <div class="blog-card__date"><?php echo $formatted_date; ?></div>
                                    <h3 class="blog-card__title"><?php echo get_button_markup(array(
                                          'href' => $permalink,
                                          'label' => $title
                                    )); ?></h3>
                                    <div class="blog-card__description">
                                          <?php echo $excerpt; ?>
                                    </div>
                                    <?php echo get_button_markup(array(
                                          'classname' => 'blog-card__button',
                                          'size' => 'md',
                                          'variant' => 'primary',
                                          'href' => $permalink,
                                          'label' => __('Read the article', 'rri-mapping-tool')
                                    )) ;?>
                              </div>
                        </li>
                        <?php endforeach; ?>

                        <li class="lg:col-start-4 lg:col-end-5 lg:row-start-2 lg:row-end-3 blog-link">
                              <img src="<?php echo $bg_image; ?>" alt="<?php echo $heading; ?>" class="blog-link__bg-img">
                              <h3 class="blog-link__tagline"><?php echo $tagline; ?></h3>
                              <?php echo get_button_markup(array(
                                    'classname' => 'blog-link__button',
                                    'size' => 'lg',
                                    'variant' => 'primary',
                                    'href' => $button['url'],
                                    'target' => $button['target'],
                                    'label' => '<span>'.$button['title'].'</span>',
                                    'suffix' => get_icon_markup('chevron-right',14)
                              )) ;?>
                        </li>
                  </ul>
            </div>
      </section>
<?php endif; ?>
<div class="rri-footer">
      <?php get_footer(); ?>
</div>