<?php

/*
|--------------------------------------------------------------------------
| Glennon Brothers: facts the site is built from
|--------------------------------------------------------------------------
| Every fact here comes from the group's own website (glennonbrothers.ie: index, aboutus,
| contactus, locations, history, products, processes, sustainability, environment, sales, the
| product pages and the press pages, read 27 October 2026), dte.ie (27 October 2026) or the
| group's Teamtailor careers site. Nothing is invented: no staff names beyond the joint
| managing directors, no headcounts, no testimonials, no certifications or awards the group
| does not publish itself. Open questions are in README.md, "Before launch".
| Read with gb('key').
*/

return [

    'name' => 'Glennon Brothers Timber Ltd',
    'short_name' => 'Glennon Brothers',
    'alternate_names' => ['Glennon Brothers', 'Glennon Bros. Timber', 'Glennon Brothers Group'],
    'founded' => 1913,
    'description' => 'Glennon Brothers is a family-run Irish timber processor, founded in Longford in 1913. Sawmills at Longford, Fermoy, Windymains and Troon supply construction timber, CLS, fencing and pallet wood to Ireland and the UK, and DTE Manufacturing makes timber frame homes and roof trusses.',
    'footer_line' => 'Sawmillers in Longford since 1913. Family run, supplying Ireland and the UK.',
    // The group's own phrase, from the 2025 TTJ award press release.
    'tagline' => 'From forest to front door',

    'phone' => '+353 43 3350800',
    'email' => env('GB_PUBLIC_EMAIL', 'info@glennonbrothers.ie'),
    'address' => [
        'street' => 'Saw Mills, Dublin Road',
        'city' => 'Longford',
        'postcode' => 'N39 AX73',
        'country' => 'Ireland',
    ],
    'website' => 'www.glennonbrothers.ie',
    'map_url' => 'https://www.google.com/maps/search/?api=1&query=Glennon+Brothers+Saw+Mills+Dublin+Road+Longford+N39+AX73',
    // The group's own pages elsewhere.
    'social' => ['facebook' => 'https://www.facebook.com/GlennonBrothers/'],

    // Concept preview: banner + noindex until the client signs off.
    'preview' => (bool) env('GB_PREVIEW', true),

    // Who is emailed about new submissions. Each area falls back to the default.
    'notify' => [
        'default' => env('GB_NOTIFY_EMAIL', ''),
        'quotes' => env('GB_NOTIFY_QUOTES', ''),
        'frame' => env('GB_NOTIFY_FRAME', ''),
        'careers' => env('GB_NOTIFY_CAREERS', ''),
        'deliveries' => env('GB_NOTIFY_DELIVERIES', ''),
        'enquiries' => env('GB_NOTIFY_ENQUIRIES', ''),
    ],

    'admin' => [
        'name' => env('ADMIN_NAME', 'Administrator'),
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],

    /*
    | The four sawmills, with the postal addresses printed on glennonbrothers.ie/contactus.html.
    | Region decides the sales team (Ireland or UK) and the currency quotes are made in.
    */
    'plants' => [
        'longford' => [
            'name' => 'Longford',
            'company' => 'Glennon Brothers Timber Ltd.',
            'role' => 'Head office and sawmill',
            'address' => ['Saw Mills, Dublin Road', 'Longford', 'N39 AX73', 'Republic of Ireland'],
            'phone' => '+353 43 3350800',
            'email' => 'info@glennonbrothers.ie',
            'region' => 'ireland',
            'since' => 1913,
            'since_label' => 'Founded 1913',
            'photo' => 'lorry',
            'photo_alt' => 'A Glennon Brothers lorry and trailer loaded with wrapped timber packs',
            'summary' => 'Where the business began in 1913, on the site of a former flax mill. Head office for the group and home to DTE Manufacturing.',
            'story' => [
                'In the late nineteenth and early twentieth century a flax mill stood on the Longford site. Brothers William and James Glennon bought the site and buildings in 1913 and set up a sawmill after returning from the United States.',
                'Motive power over the years went from water to steam to oil, and finally to electricity in 1947. The business moved from sawing hardwoods for local uses to the softwood operation it runs today, supplying both the Irish and UK markets.',
                'Longford has kiln-drying facilities, and DTE Manufacturing, the group\'s timber frame and roof truss business, shares the Dublin Road site.',
            ],
            'facts' => [
                ['Founded', '1913, by William and James Glennon'],
                ['Kiln drying', 'Yes'],
                ['On site', 'Head office; DTE Manufacturing'],
            ],
            'map' => 'https://www.google.com/maps/search/?api=1&query=Saw+Mills+Dublin+Road+Longford+N39+AX73',
        ],
        'fermoy' => [
            'name' => 'Fermoy',
            'company' => 'Glennon Brothers Cork Ltd.',
            'role' => 'Sawmill and planing',
            'address' => ['Farran South', 'Fermoy, County Cork', 'P61 Y448', 'Republic of Ireland'],
            'phone' => '+353 25 37400',
            'email' => 'info@glennonbrothers.ie',
            'region' => 'ireland',
            'since' => 1998,
            'since_label' => 'Part of the group since 1998',
            'photo' => 'fermoy-planer',
            'photo_alt' => 'The planing line inside the Fermoy mill, with sawn boards on the infeed',
            'summary' => 'A 60-acre site on the outskirts of Fermoy, bought from the Smurfit group in 1998 and rebuilt as one of the most modern sawmills in Europe.',
            'story' => [
                'Glennon Brothers bought the Fermoy plant (then Woodfab Fermoy) from the Smurfit group in 1998. The mill sits on a 60-acre site on the outskirts of the town.',
                'It was rebuilt in stages: the first stage of refurbishment in 1999, a new Linck sawline in 2001, treatment facilities and a new planing line in 2003, high-speed handling in 2004 and a new log grader in 2005. Log input is controlled by an operator in a remote control tower.',
                'Fermoy has kiln-drying facilities. In the group\'s words, the volume of timber used to run the Longford mill for a day in 1945 is now processed at Fermoy in less than five minutes.',
            ],
            'facts' => [
                ['In the group', 'Since 1998'],
                ['Site', '60 acres'],
                ['Kiln drying', 'Yes'],
            ],
            'map' => 'https://www.google.com/maps/search/?api=1&query=Glennon+Brothers+Farran+South+Fermoy+P61+Y448',
        ],
        'windymains' => [
            'name' => 'Windymains',
            'company' => 'Windymains Timber Ltd.',
            'role' => 'Fencing and treated timber mill',
            'address' => ['Windymains', 'Humbie, East Lothian', 'EH36 5PA', 'Scotland'],
            'phone' => '+44 1875 321950',
            'email' => 'wm.info@glennonbrothers.co.uk',
            'region' => 'uk',
            'since' => 2005,
            'since_label' => 'Part of the group since 2005',
            'photo' => 'windymains-tank',
            'photo_alt' => 'The red pressure-treatment vessel at Windymains, with timber packs waiting on rail bogies',
            'summary' => 'A specialist fencing and treated timber mill at Humbie, near Edinburgh, with an annual production capacity of 50,000 m³.',
            'story' => [
                'Windymains Timber, at Humbie near Edinburgh, joined the group in 2005 as the first step in its plan for the UK market.',
                'It is a specialist fencing and treated timber mill with an annual production capacity of 50,000 m³, producing boards, rails, posts and feather edge. Treatment facilities were installed after the acquisition.',
                'The plant operates under the pollution regulations of the Scottish Environment Protection Agency (SEPA).',
            ],
            'facts' => [
                ['In the group', 'Since 2005'],
                ['Capacity', '50,000 m³ a year'],
                ['Speciality', 'Fencing and treated timber'],
            ],
            'map' => 'https://www.google.com/maps/search/?api=1&query=Windymains+Timber+Humbie+EH36+5PA',
        ],
        'troon' => [
            'name' => 'Troon',
            'company' => 'Glennon Brothers (UK) Ltd',
            'role' => 'Harbour sawmill',
            'address' => ['Portland House, Harbour Road', 'Troon', 'KA10 6DN', 'Scotland'],
            'phone' => '+44 1292 800600',
            'email' => 'tr.info@glennonbrothers.co.uk',
            'region' => 'uk',
            'since' => 2008,
            'since_label' => 'Part of the group since 2008',
            'photo' => 'troon-loader',
            'photo_alt' => 'A loader carrying logs to the infeed deck at the Glennon Brothers sawmill on Troon harbour',
            'summary' => 'Formerly Adam Wilson & Sons, a family sawmill on Troon harbour. Kiln-dried carcassing, fencing, pallet and packaging timber, with its own biomass CHP plant.',
            'story' => [
                'Glennon Brothers (UK) Ltd was formerly Adam Wilson & Sons, a family business in the port of Troon, acquired in 2008. It specialises in kiln-dried carcassing, fencing, pallet and packaging material.',
                'Logs arrive by boat from the coasts and islands of Scotland to be sawn at the harbour, which cuts lorry miles. A planing line and treatment facilities were installed after the acquisition, and the mill has kiln-drying capacity.',
                'A £14 million combined heat and power (CHP) biomass plant was built in 2016–17 and first generated electricity on 17 March 2017. It powers the site and can export up to 2.5 MW to the National Grid.',
            ],
            'facts' => [
                ['In the group', 'Since 2008'],
                ['Logs arrive', 'By sea and road'],
                ['Energy', 'Biomass CHP, since 2017'],
            ],
            'map' => 'https://www.google.com/maps/search/?api=1&query=Glennon+Brothers+Portland+House+Harbour+Road+Troon+KA10+6DN',
        ],
    ],

    // DTE Manufacturing: the timber frame and roof truss business on the Longford site.
    'dte' => [
        'name' => 'DTE Manufacturing Ltd',
        'trading_as' => 'Dempsey Timber Engineering',
        'address' => ['60 Sawmills, Dublin Road', 'Longford', 'N39 AX73', 'Republic of Ireland'],
        'phone' => '+353 43 3350800',
        'email' => 'sales@dte.ie',
        'website' => 'https://dte.ie',
        'since' => 2007,
    ],

    // Sister business in Scotland for UK timber frame (glennonbrothers.ie/contactus.html).
    'atd' => [
        'name' => 'Alexanders Timber Design',
        'address' => '60 Shewalton Road, Irvine, KA11 5BN',
        'phone' => '+44 1292 679679',
        'email' => 'info@alexanderstimberdesign.co.uk',
        'website' => 'http://www.alexanderstimberdesign.com',
    ],

    /*
    | Group figures as printed on glennonbrothers.ie (aboutus.html, processes.html). Undated on
    | the site and older than the 2021 Balcas acquisition: confirm before launch.
    */
    'figures' => [
        ['value' => '450,000 m³', 'label' => 'sawn timber capacity a year, across the group', 'source' => 'glennonbrothers.ie/aboutus.html'],
        ['value' => '220,000 m³', 'label' => 'kiln-drying capacity a year, which the group describes as the largest in the British Isles', 'source' => 'glennonbrothers.ie/processes.html'],
    ],

    /*
    | Products, from glennonbrothers.ie/products.html and the product pages. Sizes in millimetres
    | exactly as the group lists them. 'quote' => the product appears on the trade quote form.
    */
    'products' => [
        'construction' => [
            'title' => 'Construction timber',
            'photo' => 'packs',
            'photo_alt' => 'Packs of kiln-dried sawn timber stacked in the yard',
            'intro' => 'Kiln-dried, strength-graded carcassing for builders\' merchants, and CLS for the timber frame industry. Kiln drying increases strength, cuts weight and handling costs, and keeps shrinking and swelling in use to manageable amounts.',
            'items' => [
                ['name' => 'Kiln-dried strength-graded carcassing', 'detail' => 'For general construction, sold through builders\' merchants in Ireland and the UK.'],
                ['name' => 'CLS (Canadian Lumber Standard)', 'detail' => 'Planed all over with eased edges. 140 × 38 for external walls and 89 × 38 for internal walls, in lengths from 2.4 m to 5.4 m. Graded to IS 444, mostly to C18. Available treated (Protim Clearchoice) and precision end-trimmed.'],
                ['name' => 'Machined whitewoods', 'detail' => 'For the construction and garden shed industries.'],
            ],
            'note' => 'Glennon Brothers was the first, and is still the only, Irish sawmill to produce volumes of CLS from home-grown timber (the group\'s own statement).',
        ],
        'fencing' => [
            'title' => 'Fencing',
            'photo' => 'fencing',
            'photo_alt' => 'A close-boarded timber fence in a garden',
            'intro' => 'Posts, rails and boards, and the Glenfence range of panels and sleepers sold through builders\' providers. All fencing is pressure treated with Osmose Naturewood.',
            'items' => [
                ['name' => 'Posts, pointed or unpointed', 'detail' => '100 × 100, 100 × 75, 75 × 75.'],
                ['name' => 'Rails', 'detail' => '100 × 35 and 150 × 35 D rail; 75 × 44 slay rail.'],
                ['name' => 'Boards', 'detail' => '150 × 22 and 100 × 22 weather edge, round top, ribbed round top and champagne top.'],
                ['name' => 'Glenfence panels and sleepers', 'detail' => 'Closed panels 1.8 × 1.8 m and 1.8 × 1.5 m; ribbed picket panels 1.8 × 1.0 m and 1.8 × 1.2 m; 200 × 100 sleepers in 2.4 m and 3.0 m.'],
            ],
            'note' => 'Windymains is a specialist fencing mill producing boards, rails, posts and feather edge.',
        ],
        'pallet' => [
            'title' => 'Pallet and packaging timber',
            'photo' => 'troon-yard',
            'photo_alt' => 'Packs of sawn timber in the yard at Troon',
            'intro' => 'Raw material for pallets and packing cases, in cross-cut lengths from 0.8 m upwards, kiln dried or unseasoned. Glennon Brothers was one of the first sawmills in the British Isles certified for ISPM 15, the international standard for wood packaging.',
            'items' => [
                ['name' => 'Regular sizes, Irish mills', 'detail' => 'Widths 75, 95, 100, 125 and 150 in thicknesses of 16, 18 and 22.'],
                ['name' => 'Regular sizes, Troon', 'detail' => 'Widths 75, 95, 120 and 150 in 18 thick.'],
                ['name' => 'Heat treatment', 'detail' => 'Certified for heat treatment since 2002.'],
            ],
            'note' => null,
        ],
        'decking' => [
            'title' => 'Glendeck decking',
            'photo' => null,
            'photo_alt' => null,
            'intro' => 'Glendeck boards and accessories made from treated home-grown timber, pressure treated with Osmose Naturewood.',
            'items' => [
                ['name' => 'Boards', 'detail' => '150 × 44, 150 × 35 and 125 × 38, with a double-sided profile.'],
                ['name' => 'Accessories', 'detail' => 'Spindles, handrail, base rail and posts, including turned newel posts.'],
                ['name' => 'Certification', 'detail' => 'Glendeck boards are FSC® certified; accessories are FSC or PEFC certified.'],
            ],
            'note' => null,
        ],
        'residues' => [
            'title' => 'Wood chip, sawdust, bark and shavings',
            'photo' => null,
            'photo_alt' => null,
            'intro' => 'Every part of the log is used. End-use markets have been developed for all the by-products of sawing.',
            'items' => [
                ['name' => 'Wood chip', 'detail' => 'Mostly for MDF board manufacture.'],
                ['name' => 'Sawdust', 'detail' => 'Fuels the kilns, and goes into chipboard and animal bedding.'],
                ['name' => 'Bark', 'detail' => 'Processed into garden mulch.'],
                ['name' => 'Shavings', 'detail' => 'Bedding for the horse and poultry industries.'],
            ],
            'note' => null,
        ],
    ],

    // Products a trade customer can ask a price for (QuoteLine::product).
    'quote_products' => [
        'carcassing' => ['label' => 'Kiln-dried carcassing (construction)', 'group' => 'Construction'],
        'cls' => ['label' => 'CLS', 'group' => 'Construction'],
        'whitewood' => ['label' => 'Machined whitewood', 'group' => 'Construction'],
        'fence_post' => ['label' => 'Fence posts', 'group' => 'Fencing'],
        'fence_rail' => ['label' => 'Fence rails (D rail, slay rail)', 'group' => 'Fencing'],
        'fence_board' => ['label' => 'Fence boards (weather edge, round top)', 'group' => 'Fencing'],
        'glenfence' => ['label' => 'Glenfence panels or sleepers', 'group' => 'Fencing'],
        'pallet' => ['label' => 'Pallet and packaging timber', 'group' => 'Pallet and packaging'],
        'glendeck' => ['label' => 'Glendeck decking or accessories', 'group' => 'Other'],
        'residues' => ['label' => 'Wood chip, sawdust, bark or shavings', 'group' => 'Other'],
        'other' => ['label' => 'Something else (describe it)', 'group' => 'Other'],
    ],

    /*
    | Sustainability, from glennonbrothers.ie/substainability.html, enviromental.html and the
    | Glendeck page. Certificate codes exactly as the group prints them: check them against the
    | FSC public certificate database before launch (NEPCon now trades as Preferred by Nature).
    */
    'certifications' => [
        ['title' => 'FSC® Chain of Custody, Irish plants', 'body' => 'Certified by NEPCon. Chain of Custody code NC-COC-014377, Controlled Wood code NC-CW-014377, trademark licence code FSC-C006033.'],
        ['title' => 'FSC® Chain of Custody, UK plants', 'body' => 'Windymains and Troon hold a multi-site certificate verified by Control Union: Chain of Custody code CU-COC-856459, trademark licence code FSC-C021680.'],
        ['title' => 'Integrated Pollution Control licence', 'body' => 'Glennon Brothers was one of the first sawmills in Ireland to receive an IPC licence, independently monitored by the Environmental Protection Agency.'],
        ['title' => 'SEPA', 'body' => 'Windymains and Troon operate under the pollution regulations of the Scottish Environment Protection Agency.'],
        ['title' => 'ISPM 15', 'body' => 'One of the first sawmills in the British Isles certified for ISPM 15, the international standard for wood packaging material.'],
        ['title' => 'NSAI (timber frame)', 'body' => 'DTE is approved and monitored by the National Standards Authority of Ireland under its quality scheme for timber frame.'],
    ],
    // Environmental Product Declarations, linked on the group's environment page.
    'epds' => [
        ['label' => 'Kiln-dried timber', 'url' => 'https://glennonbrothers.ie/Kiln-Dried.pdf'],
        ['label' => 'Green sawn timber', 'url' => 'https://glennonbrothers.ie/Green-Sawn.pdf'],
        ['label' => 'Treated timber', 'url' => 'https://glennonbrothers.ie/Treated-Timber.pdf'],
    ],

    /*
    | About. The joint managing directors are named on glennonbrothers.ie/aboutus.html and
    | ourpeople.html. The story is the group's own, lightly trimmed.
    */
    'directors' => [
        'names' => 'Pat Glennon and Mike Glennon',
        'title' => 'Joint Managing Directors',
        'text' => 'Paddy Glennon ran the business for over 50 years after succeeding his father William in 1943. His sons Pat and Mike became joint managing directors in the mid-1990s and grew the group with the acquisition of Fermoy (1998), Windymains (2005), Dempsey Timber Engineering (2007), Adam Wilson & Sons and Alexanders Timber Design (2008) and Balcas (2021).',
        'source' => 'glennonbrothers.ie/aboutus.html',
    ],
    // Group brands as listed on the group's careers site (Teamtailor), October 2026.
    'brands' => ['Glennon Brothers', 'Balcas', 'DTE', 'Alexanders Timber Design', 'Pontrilas'],

    // From glennonbrothers.ie/history.html (2021 Balcas line confirmed by the 29 October 2021 press release).
    'timeline' => [
        ['1913', 'Glennon Brothers Timber Ltd founded in Longford by William and James Glennon.'],
        ['1938', 'Power supply changes from water to steam.'],
        ['1945', 'Sawmill power changes from steam to electricity.'],
        ['1976', 'Sources the timber for Tim Severin\'s boat for the "Brendan Voyage".'],
        ['1981', 'Sawdust-fired drying kilns installed.'],
        ['1991', 'Production of CLS for the timber frame manufacturing industry.'],
        ['1998', 'Acquires Woodfab Fermoy from the Smurfit group; launches Glendeck.'],
        ['2001', 'New Linck sawline in Fermoy.'],
        ['2005', 'Windymains Timber Ltd established in Scotland; Glenfence launched.'],
        ['2007', 'Acquires Dempsey Timber Engineering.'],
        ['2008', 'Acquires Adam Wilson & Sons, Troon, and Alexanders Timber Design.'],
        ['2013', 'Centenary, marked at Croke Park; Ruban d\'Honneur, European Business Awards.'],
        ['2017', 'Troon\'s biomass CHP plant generates electricity for the first time, 17 March.'],
        ['2019', 'TTJ Softwood Trader of the Year; HAI Best Supplier Customer Experience Award.'],
        ['2021', 'Completes the acquisition of Balcas Timber and Balcas Energy.'],
        ['2025', 'TTJ Softwood Trader: Sawmiller of the Year, for the sixth time.'],
    ],

    // Awards the group publishes on its own press and history pages.
    'awards' => [
        ['year' => '2025', 'title' => 'Softwood Trader: Sawmiller of the Year', 'by' => 'TTJ Awards', 'note' => 'Sixth win', 'url' => 'https://glennonbrothers.ie/press/TTJ_Awards_2025.html'],
        ['year' => '2019–2023', 'title' => 'Softwood Trader of the Year', 'by' => 'TTJ Awards', 'note' => 'Five years in a row', 'url' => 'https://glennonbrothers.ie/press/TTJ_Awards_2023.html'],
        ['year' => '2019', 'title' => 'Best Supplier Customer Experience', 'by' => 'HAI Awards', 'note' => null, 'url' => 'https://glennonbrothers.ie/press/HAICustomerAward.html'],
        ['year' => '2013', 'title' => 'Ruban d\'Honneur', 'by' => 'European Business Awards', 'note' => null, 'url' => 'https://glennonbrothers.ie/press/Ruban_dHonneur.html'],
    ],

    // News: the group's own press page (glennonbrothers.ie/news.html), newest first.
    'news' => [
        ['date' => '2025-09-19', 'title' => 'Sawmiller of the Year for a sixth time', 'summary' => 'Glennon Brothers won the TTJ \'Softwood Trader: Sawmiller of the Year\' award again, voted by customers across Europe, the UK and Ireland, after winning it five years in a row from 2019 to 2023.', 'url' => 'https://glennonbrothers.ie/press/TTJ_Awards_2025.html', 'photo' => 'ttj-2025'],
        ['date' => '2025-05-29', 'title' => 'Sponsoring the New Commercial Woodland Award', 'summary' => 'Glennon Brothers sponsors the New Commercial Woodland Award in the Quality Timber section of Scotland\'s Finest Woods Awards.', 'url' => 'https://glennonbrothers.ie/press/Sponsor%20New%20Commercial%20Woodland%20Award.html', 'photo' => null],
        ['date' => '2024-12-06', 'title' => 'The first group Movember challenge', 'summary' => '2024 saw the first Glennon Brothers Group #Movember challenge, with participants and supporters across the sites.', 'url' => 'https://glennonbrothers.ie/press/Movember2024.html', 'photo' => null],
        ['date' => '2024-03-08', 'title' => 'Longford Ladies sponsorship renewed', 'summary' => 'Glennon Brothers renewed its sponsorship of the Longford Ladies Gaelic Football Association for another three years.', 'url' => 'https://glennonbrothers.ie/press/LGFA_Sponsorship_Extension_2024.html', 'photo' => null],
        ['date' => '2024-02-08', 'title' => 'Hosting the TTJ Career Development finalists', 'summary' => 'Glennon Brothers hosted the 2023 TTJ Career Development Award trip, a tour of the Irish forestry and timber industries.', 'url' => 'https://glennonbrothers.ie/press/TJJ_Career_Development_Awards.html', 'photo' => null],
        ['date' => '2023-09-15', 'title' => 'Sawmiller of the Year, five years running', 'summary' => 'A fifth consecutive TTJ \'Softwood Trader: Sawmiller of the Year\' award, presented at the Hilton Bankside, London.', 'url' => 'https://glennonbrothers.ie/press/TTJ_Awards_2023.html', 'photo' => null],
        ['date' => '2021-10-29', 'title' => 'Balcas acquisition completed', 'summary' => 'Glennon Brothers completed its acquisition of Balcas from SHV Energy, adding the Enniskillen sawmill and Balcas Energy\'s renewable plants at Enniskillen and Invergordon.', 'url' => 'https://glennonbrothers.ie/press/Glennons_Balcas_Aquisition.html', 'photo' => null],
        ['date' => '2017-03-31', 'title' => 'Cabinet Secretary visits Troon', 'summary' => 'Fergus Ewing MSP visited the Troon plant to see the construction of the £14 million biomass CHP facility.', 'url' => 'https://glennonbrothers.ie/press/Cabinet_Minister_Ewing_visits_Troon_2017.html', 'photo' => 'troon-visit'],
    ],

    // Careers: what the groups of roles involve (from the group's current vacancies on Teamtailor).
    'careers' => [
        'points' => [
            ['title' => 'Four mills, one application', 'text' => 'Choose a plant, or tell us you would work at any of them. Your application goes to the HR team, who pass it to the plant manager.'],
            ['title' => 'No CV needed for operatives and drivers', 'text' => 'Yard, mill and timber frame roles need only a phone number and a few details. Maintenance, electrical and office roles attach a CV.'],
            ['title' => 'You hear back', 'text' => 'Every application gets a reference number by email, and HR passes it to the manager at the plant you chose.'],
        ],
    ],

    // Irish counties plus the UK nations, for addresses and applications.
    'counties' => [
        'Republic of Ireland' => ['Carlow', 'Cavan', 'Clare', 'Cork', 'Donegal', 'Dublin', 'Galway', 'Kerry', 'Kildare', 'Kilkenny', 'Laois', 'Leitrim', 'Limerick', 'Longford', 'Louth', 'Mayo', 'Meath', 'Monaghan', 'Offaly', 'Roscommon', 'Sligo', 'Tipperary', 'Waterford', 'Westmeath', 'Wexford', 'Wicklow'],
        'Northern Ireland' => ['Antrim', 'Armagh', 'Down', 'Fermanagh', 'Derry/Londonderry', 'Tyrone'],
    ],

    /*
    | Photos: public/img/{key}-{width}.webp. Credits in config/photo-credits.php. 'own' photos
    | are the group's (glennonbrothers.ie). Wikimedia Commons photos of the Troon and Windymains
    | mills show the group's own plants; 'forest' and 'frame-house' are illustrative.
    */
    'photos' => [
        'fermoy-planer' => ['w' => 2400, 'h' => 1462],
        'windymains-tank' => ['w' => 1280, 'h' => 960],
        'directors' => ['w' => 580, 'h' => 386],
        'centenary' => ['w' => 2400, 'h' => 1600],
        'troon-visit' => ['w' => 2048, 'h' => 1536],
        'lorry' => ['w' => 800, 'h' => 250],
        'trusses' => ['w' => 800, 'h' => 250],
        'fencing' => ['w' => 800, 'h' => 250],
        'packs' => ['w' => 800, 'h' => 250],
        'operator' => ['w' => 799, 'h' => 249],
        'ttj-2025' => ['w' => 509, 'h' => 382],
        'troon-harbour' => ['w' => 2400, 'h' => 1600],
        'troon-loader' => ['w' => 2400, 'h' => 1600],
        'troon-cyclones' => ['w' => 2400, 'h' => 1600],
        'troon-stacks' => ['w' => 2400, 'h' => 1800],
        'troon-yard' => ['w' => 1024, 'h' => 683],
        'troon-entrance' => ['w' => 2400, 'h' => 1653],
        'troon-mill' => ['w' => 2400, 'h' => 1600],
        'troon-night' => ['w' => 1600, 'h' => 921],
        'windymains-aerial' => ['w' => 1024, 'h' => 768],
        'forest' => ['w' => 2400, 'h' => 1800],
        'frame-house' => ['w' => 2400, 'h' => 1350],
    ],
    // Photos that do not show a Glennon Brothers plant or product: labelled "Illustrative photo".
    'illustrative_photos' => ['forest', 'frame-house'],
    'og_photo' => 'troon-harbour',

    // Pages on the current static site, still linked from directories and bookmarks.
    'legacy_redirects' => [
        '/index.html' => '/',
        '/aboutus.html' => '/about',
        '/ourpeople.html' => '/about',
        '/history.html' => '/about#history',
        '/contactus.html' => '/contact',
        '/locations.html' => '/plants',
        '/products.html' => '/products',
        '/CLS.html' => '/products#construction',
        '/contimber.html' => '/products#construction',
        '/fencing.html' => '/products#fencing',
        '/glenfence.html' => '/products#fencing',
        '/paletwood.html' => '/products#pallet',
        '/decking.html' => '/products#decking',
        '/glendeck.html' => '/products#decking',
        '/processes.html' => '/products#processes',
        '/sales.html' => '/trade-quote',
        '/timberframing.html' => '/timber-frame',
        '/rooftrusses.html' => '/timber-frame#trusses',
        '/substainability.html' => '/sustainability',
        '/enviromental.html' => '/sustainability',
        '/forestry.html' => '/sustainability',
        '/news.html' => '/news',
        '/careers.html' => '/careers',
        '/privacyir.html' => '/privacy',
    ],
];
