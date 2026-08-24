<?php
/**
 * Fictional demo newsroom catalog (not real reporting).
 *
 * @package InfyNewsOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Static demo dataset.
 */
class INOS_Demo_Catalog {

	/**
	 * Parent sections.
	 *
	 * @return array<string, array<string, string>>
	 */
	public static function categories() {
		return array(
			'technology' => array(
				'name'        => __( 'Technology', 'infy-news-os-core' ),
				'description' => __( 'Computing, AI, gadgets, and the networks that run them.', 'infy-news-os-core' ),
				'color'       => '#0b3d5c',
			),
			'business'   => array(
				'name'        => __( 'Business', 'infy-news-os-core' ),
				'description' => __( 'Markets, companies, work, and the economy.', 'infy-news-os-core' ),
				'color'       => '#1a3d2f',
			),
			'science'    => array(
				'name'        => __( 'Science', 'infy-news-os-core' ),
				'description' => __( 'Research, climate, space, and public health.', 'infy-news-os-core' ),
				'color'       => '#3d2a0b',
			),
			'world'      => array(
				'name'        => __( 'World', 'infy-news-os-core' ),
				'description' => __( 'International news and policy.', 'infy-news-os-core' ),
				'color'       => '#2c1a3d',
			),
			'culture'    => array(
				'name'        => __( 'Culture', 'infy-news-os-core' ),
				'description' => __( 'Books, film, games, and ideas.', 'infy-news-os-core' ),
				'color'       => '#5c2a1a',
			),
			'opinion'    => array(
				'name'        => __( 'Opinion', 'infy-news-os-core' ),
				'description' => __( 'Arguments from the newsroom and guests.', 'infy-news-os-core' ),
				'color'       => '#1a1a1a',
			),
		);
	}

	/**
	 * Tags.
	 *
	 * @return array<string, string>
	 */
	public static function tags() {
		return array(
			'ai'         => __( 'AI', 'infy-news-os-core' ),
			'chips'      => __( 'Semiconductors', 'infy-news-os-core' ),
			'climate'    => __( 'Climate', 'infy-news-os-core' ),
			'startups'   => __( 'Startups', 'infy-news-os-core' ),
			'policy'     => __( 'Policy', 'infy-news-os-core' ),
			'space'      => __( 'Space', 'infy-news-os-core' ),
			'cybersecurity' => __( 'Cybersecurity', 'infy-news-os-core' ),
			'energy'        => __( 'Energy', 'infy-news-os-core' ),
			'health'        => __( 'Health', 'infy-news-os-core' ),
		);
	}

	/**
	 * Demo authors (created as WordPress users).
	 *
	 * @return array<string, array<string, string>>
	 */
	public static function authors() {
		return array(
			'priya' => array(
				'login'        => 'inos_demo_priya',
				'email'        => 'priya.demo@example.com',
				'name'         => 'Priya Menon',
				'job'          => __( 'Senior correspondent', 'infy-news-os-core' ),
				'expertise'    => __( 'AI, semiconductors, industrial policy', 'infy-news-os-core' ),
				'bio'          => __( 'Priya covers computing and industrial policy from Bengaluru. She previously reported on chip supply chains for a national daily and was a Knight Science Journalism fellow.', 'infy-news-os-core' ),
				'short_bio'    => __( 'Covers computing, chips, and industrial policy from Bengaluru.', 'infy-news-os-core' ),
				'location'     => 'Bengaluru',
				'credentials'  => __( 'Knight Science Journalism Fellow, MIT', 'infy-news-os-core' ),
				'awards'       => __( 'Ramnath Goenka Award', 'infy-news-os-core' ),
				'languages'    => __( 'English, Hindi, Malayalam', 'infy-news-os-core' ),
				'twitter'      => 'priyamenon_demo',
				'linkedin'     => 'https://www.linkedin.com/in/priya-menon-demo',
				'sameas'       => "https://example.com/staff/priya-menon",
				'started_year' => '2016',
				'show_email'   => '1',
			),
			'arjun' => array(
				'login'        => 'inos_demo_arjun',
				'email'        => 'arjun.demo@example.com',
				'name'         => 'Arjun Desai',
				'job'          => __( 'Science editor', 'infy-news-os-core' ),
				'expertise'    => __( 'Climate, space, public health', 'infy-news-os-core' ),
				'bio'          => __( 'Arjun edits science coverage and writes explainers on climate and spaceflight. He trained as an atmospheric physicist before joining the newsroom.', 'infy-news-os-core' ),
				'short_bio'    => __( 'Science editor covering climate, space, and public health.', 'infy-news-os-core' ),
				'location'     => 'New Delhi',
				'credentials'  => __( 'PhD, atmospheric physics', 'infy-news-os-core' ),
				'awards'       => '',
				'languages'    => __( 'English, Gujarati, Hindi', 'infy-news-os-core' ),
				'twitter'      => 'arjundesai_demo',
				'linkedin'     => '',
				'sameas'       => 'https://example.com/staff/arjun-desai',
				'started_year' => '2014',
				'show_email'   => '1',
			),
			'maya'  => array(
				'login'        => 'inos_demo_maya',
				'email'        => 'maya.demo@example.com',
				'name'         => 'Maya Krishnan',
				'job'          => __( 'Culture correspondent', 'infy-news-os-core' ),
				'expertise'    => __( 'Film, books, games', 'infy-news-os-core' ),
				'bio'          => __( 'Maya reports on culture and the business of entertainment, from streaming economics to festival premieres.', 'infy-news-os-core' ),
				'short_bio'    => __( 'Reports on film, books, games, and the business of entertainment.', 'infy-news-os-core' ),
				'location'     => 'Mumbai',
				'credentials'  => '',
				'awards'       => '',
				'languages'    => __( 'English, Tamil', 'infy-news-os-core' ),
				'twitter'      => 'mayakrishnan_demo',
				'linkedin'     => '',
				'sameas'       => 'https://example.com/staff/maya-krishnan',
				'started_year' => '2019',
				'show_email'   => '0',
			),
		);
	}

	/**
	 * Articles.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function posts() {
		return array(
			array(
				'slug'         => 'inos-demo-india-chip-fabs',
				'title'        => __( 'India’s first advanced chip fab clears final environmental review', 'infy-news-os-core' ),
				'kicker'       => __( 'Semiconductors', 'infy-news-os-core' ),
				'dek'          => __( 'The Gujarat plant can now order lithography tools. Production is still years away — and the water plan is already under scrutiny.', 'infy-news-os-core' ),
				'dateline'     => 'GANDHINAGAR',
				'category'     => 'technology',
				'tags'         => array( 'chips', 'policy' ),
				'type'         => 'news',
				'author'       => 'priya',
				'hours_ago'    => 2,
				'breaking'     => 1,
				'exclusive'    => 1,
				'homepage_pin' => 1,
				'trending_pin' => 1,
				'views'        => 8120,
				'paragraphs'   => array(
					__( 'Gujarat’s environment ministry has approved the last remaining clearance for a proposed advanced logic fab, according to three people familiar with the filing and a letter reviewed by this newsroom.', 'infy-news-os-core' ),
					__( 'The decision lets the consortium place purchase orders for extreme ultraviolet lithography tools — machines with lead times measured in years. Officials described the vote as unanimous after the company agreed to a recycled-water target of 90 percent.', 'infy-news-os-core' ),
					__( 'Local farmers’ groups say the target is not enforceable. “A letter of intent is not a pipeline,” said Meera Solanki, who organizes irrigators south of the site. The company declined to comment beyond a statement that construction would follow “all applicable law.”', 'infy-news-os-core' ),
					__( 'New Delhi has treated semiconductor manufacturing as a strategic industry since 2023. Even with this clearance, engineers caution that a working process node is a separate, harder problem than pouring concrete.', 'infy-news-os-core' ),
				),
			),
			array(
				'slug'       => 'inos-demo-model-lab-leak',
				'title'      => __( 'Open-weight model leak traced to a misconfigured object store', 'infy-news-os-core' ),
				'kicker'     => __( 'Artificial intelligence', 'infy-news-os-core' ),
				'dek'        => __( 'Weights for a 70-billion-parameter assistant were downloadable for 11 hours. The lab says no customer data sat in the same bucket.', 'infy-news-os-core' ),
				'dateline'   => 'SAN FRANCISCO',
				'category'   => 'technology',
				'tags'       => array( 'ai', 'cybersecurity' ),
				'type'       => 'news',
				'author'     => 'priya',
				'hours_ago'  => 5,
				'breaking'   => 1,
				'views'      => 6400,
				'paragraphs' => array(
					__( 'A research lab’s latest open-weight language model was briefly reachable on a public cloud bucket after a staging script dropped an access policy, according to a post-mortem published Thursday.', 'infy-news-os-core' ),
					__( 'Security researchers mirrored the files within the hour. The lab says the snapshot matches a model it planned to release next month, and that the bucket did not contain prompts, fine-tunes, or customer logs.', 'infy-news-os-core' ),
					__( 'The incident will fuel a running argument in the industry: whether “open weights” are a safety feature or an accelerant. Policymakers in Brussels and New Delhi had already asked labs to inventory who can pull frontier checkpoints.', 'infy-news-os-core' ),
				),
			),
			array(
				'slug'         => 'inos-demo-phone-satellite',
				'title'        => __( 'Flagship phones will ship with two-way satellite messaging as a default', 'infy-news-os-core' ),
				'kicker'       => __( 'Gadgets', 'infy-news-os-core' ),
				'dek'          => __( 'Three Android makers confirmed the radios. Coverage still depends on partners, and emergency SOS remains the only guaranteed mode.', 'infy-news-os-core' ),
				'dateline'     => 'TAIPEI',
				'category'     => 'technology',
				'tags'         => array( 'ai' ),
				'type'         => 'news',
				'author'       => 'priya',
				'hours_ago'    => 8,
				'trending_pin' => 1,
				'views'        => 4100,
				'paragraphs'   => array(
					__( 'Next year’s flagship Android phones will include two-way satellite messaging without a separate accessory, three manufacturers told this newsroom on the sidelines of a component show here.', 'infy-news-os-core' ),
					__( 'The feature will sit behind the same emergency SOS toggle already familiar on some devices. Casual chatting over satellite will require a carrier add-on, and speeds will remain closer to a pager than a smartphone.', 'infy-news-os-core' ),
				),
			),
			array(
				'slug'       => 'inos-demo-browser-memory',
				'title'      => __( 'Why your laptop fan is loud: browsers quietly doubled RAM use', 'infy-news-os-core' ),
				'kicker'     => __( 'Explainer', 'infy-news-os-core' ),
				'dek'        => __( 'Site isolation, video, and AI sidebars all spend memory. Here is what actually changed in two years.', 'infy-news-os-core' ),
				'dateline'   => 'BENGALURU',
				'category'   => 'technology',
				'tags'       => array( 'ai' ),
				'type'       => 'explainer',
				'author'     => 'arjun',
				'hours_ago'  => 11,
				'views'      => 3800,
				'paragraphs' => array(
					__( 'If your notebook sounds like a small aircraft on a video call, you are not imagining it. Independent tests we commissioned show median RAM use for a dozen-tab workday rose from about 3.1 GB to 6.4 GB between 2024 and 2026.', 'infy-news-os-core' ),
					__( 'The largest slice is site isolation — a security win after a decade of speculative-execution bugs. The second is media: 4K previews and background conferencing keep decoders warm. The third, and newest, is on-device AI sidebars that load a model even when you never click them.', 'infy-news-os-core' ),
					__( 'You can claw back memory by disabling unused AI panels, capping background video, and using a separate browser profile for mail. None of that is as satisfying as blaming a single company. It is also more accurate.', 'infy-news-os-core' ),
				),
			),
			array(
				'slug'       => 'inos-demo-payments-upi-fee',
				'title'      => __( 'Large merchants will pay a small UPI fee from October, draft rules show', 'infy-news-os-core' ),
				'kicker'     => __( 'Payments', 'infy-news-os-core' ),
				'dek'        => __( 'Person-to-person transfers stay free. Marketplaces above a turnover threshold would fund the rails.', 'infy-news-os-core' ),
				'dateline'   => 'MUMBAI',
				'category'   => 'business',
				'tags'       => array( 'policy', 'startups' ),
				'type'       => 'news',
				'author'     => 'priya',
				'hours_ago'  => 4,
				'views'      => 5200,
				'trending_pin' => 1,
				'paragraphs' => array(
					__( 'India’s payments regulator is preparing a modest interchange fee on UPI transactions for large online merchants, according to a draft circulated to banks this week and seen by this newsroom.', 'infy-news-os-core' ),
					__( 'Peer-to-peer transfers and neighborhood shops would remain free. The proposal targets marketplaces and streaming services above an annual turnover threshold, with proceeds meant to keep the switch and fraud systems funded.', 'infy-news-os-core' ),
					__( 'Startup groups argue any fee will be passed to sellers. Banks argue the current model socializes costs onto depositors. A public consultation is expected before the October target.', 'infy-news-os-core' ),
				),
			),
			array(
				'slug'       => 'inos-demo-startup-layoff',
				'title'      => __( 'Quick-commerce unicorn cuts 12% of staff after a brutal quarter', 'infy-news-os-core' ),
				'kicker'     => __( 'Startups', 'infy-news-os-core' ),
				'dek'        => __( 'Same-day grocery looked inevitable. Unit economics did not.', 'infy-news-os-core' ),
				'dateline'   => 'GURUGRAM',
				'category'   => 'business',
				'tags'       => array( 'startups' ),
				'type'       => 'news',
				'author'     => 'maya',
				'hours_ago'  => 7,
				'views'      => 2900,
				'paragraphs' => array(
					__( 'A well-known quick-commerce company told staff on Thursday that it would eliminate about 12 percent of roles, concentrating cuts in expansion cities where dark stores never reached density.', 'infy-news-os-core' ),
					__( 'Internal slides, described by two employees, show contribution margins still negative after two years of subsidies. Investors have asked for a path to cash-flow breakeven before any new fundraising.', 'infy-news-os-core' ),
				),
			),
			array(
				'slug'       => 'inos-demo-rupee-bonds',
				'title'      => __( 'Foreign funds quietly returned to rupee bonds. Here is why it matters', 'infy-news-os-core' ),
				'kicker'     => __( 'Markets', 'infy-news-os-core' ),
				'dek'        => __( 'Inflows are not a victory lap. They are a bet that inflation stays boring.', 'infy-news-os-core' ),
				'dateline'   => 'MUMBAI',
				'category'   => 'business',
				'tags'       => array( 'policy' ),
				'type'       => 'analysis',
				'author'     => 'priya',
				'hours_ago'  => 14,
				'views'      => 2100,
				'paragraphs' => array(
					__( 'Overseas investors added rupee government bonds for a fourth straight week, the longest streak in a year. The sums are not huge. The signal is.', 'infy-news-os-core' ),
					__( 'When global funds buy duration in India they are underwriting two stories at once: that the central bank will not have to hike in a hurry, and that the currency will not become a one-way trade against the dollar.', 'infy-news-os-core' ),
					__( 'Neither story is guaranteed. A monsoon shock or a messy global risk-off can reverse the tape in a session. For now, the bid is real — and it cheapens borrowing for everyone else.', 'infy-news-os-core' ),
				),
			),
			array(
				'slug'       => 'inos-demo-boardroom-ai',
				'title'      => __( 'Interview: a CFO on what “AI productivity” looks like in the ledger', 'infy-news-os-core' ),
				'kicker'     => __( 'Interview', 'infy-news-os-core' ),
				'dek'        => __( 'Less romance, more invoice matching. And a hard no on letting a model talk to the bank.', 'infy-news-os-core' ),
				'dateline'   => 'HYDERABAD',
				'category'   => 'business',
				'tags'       => array( 'ai', 'startups' ),
				'type'       => 'interview',
				'author'     => 'priya',
				'hours_ago'  => 18,
				'views'      => 1800,
				'paragraphs' => array(
					__( 'Nandita Rao runs finance for a mid-size software exporter. She agreed to speak on the record about the first year of “enterprise AI” after the slogans faded.', 'infy-news-os-core' ),
					__( '“We saved money in accounts payable. We did not save money in strategy offsites,” she said. Invoice matching and contract clause search were the two tools that survived a quarterly kill list.', 'infy-news-os-core' ),
					__( 'Asked whether a model would ever initiate a payment, Rao laughed. “Not while I am employed here. A human still clicks send.”', 'infy-news-os-core' ),
				),
			),
			array(
				'slug'       => 'inos-demo-monsoon-models',
				'title'      => __( 'New monsoon models are better. Farmers still need a forecast they can use', 'infy-news-os-core' ),
				'kicker'     => __( 'Climate', 'infy-news-os-core' ),
				'dek'        => __( 'Skill scores improved. The last mile — a village-scale, five-day outlook in the language people farm in — did not.', 'infy-news-os-core' ),
				'dateline'   => 'PUNE',
				'category'   => 'science',
				'tags'       => array( 'climate' ),
				'type'       => 'analysis',
				'author'     => 'arjun',
				'hours_ago'  => 6,
				'trending_pin' => 1,
				'views'      => 3300,
				'paragraphs' => array(
					__( 'India’s latest coupled ocean-atmosphere models reduced error on seasonal rainfall compared with the 2020 vintage, according to a verification paper accepted this month.', 'infy-news-os-core' ),
					__( 'That is genuine scientific progress. It is not yet an agricultural product. Extension officers still translate a national map into advice, often a day late, often in a language the bulletin was not written in.', 'infy-news-os-core' ),
					__( 'Several state experiments now push five-day district outlooks over WhatsApp. The ones that work hire humans to interpret the model, not the other way around.', 'infy-news-os-core' ),
				),
			),
			array(
				'slug'       => 'inos-demo-heat-vaccine',
				'title'      => __( 'A dengue trial paused after unexpected fever in a small adult cohort', 'infy-news-os-core' ),
				'kicker'     => __( 'Public health', 'infy-news-os-core' ),
				'dek'        => __( 'Regulators called it a precaution. Researchers say the signal is not yet a finding.', 'infy-news-os-core' ),
				'dateline'   => 'CHENNAI',
				'category'   => 'science',
				'tags'       => array(),
				'type'       => 'news',
				'author'     => 'arjun',
				'hours_ago'  => 9,
				'views'      => 2700,
				'paragraphs' => array(
					__( 'An independent board paused enrollment in a mid-stage dengue vaccine trial after a cluster of prolonged fevers in adults over 45, the sponsor said Thursday.', 'infy-news-os-core' ),
					__( 'No deaths were reported. The company stressed that a pause is a safety system working, not a verdict on the shot. Independent virologists we contacted said they wanted the unblinded tables before drawing conclusions.', 'infy-news-os-core' ),
				),
			),
			array(
				'slug'       => 'inos-demo-lunar-sample',
				'title'      => __( 'Lunar samples show a surprisingly wet pocket of the Moon’s south pole', 'infy-news-os-core' ),
				'kicker'     => __( 'Space', 'infy-news-os-core' ),
				'dek'        => __( 'That does not mean a base is easy. It does mean ice maps need another revision.', 'infy-news-os-core' ),
				'dateline'   => 'BENGALURU',
				'category'   => 'science',
				'tags'       => array( 'space' ),
				'type'       => 'news',
				'author'     => 'arjun',
				'hours_ago'  => 16,
				'views'      => 4500,
				'trending_pin' => 1,
				'paragraphs' => array(
					__( 'Spectra from a recent lander suggest a higher concentration of hydrogen-bearing minerals than orbital maps implied, scientists said at a briefing.', 'infy-news-os-core' ),
					__( 'Ice still has to be extractable, processable, and worth the mass of the machinery. The finding mainly tells mission planners their models were dry — which, in planetary science, is a kind of good news.', 'infy-news-os-core' ),
				),
			),
			array(
				'slug'       => 'inos-demo-antibiotic-soil',
				'title'      => __( 'A soil microbe from the Western Ghats yields a promising antibiotic lead', 'infy-news-os-core' ),
				'kicker'     => __( 'Biology', 'infy-news-os-core' ),
				'dek'        => __( 'It kills a stubborn lab strain. It is not a drug. Those are different sentences.', 'infy-news-os-core' ),
				'dateline'   => 'BENGALURU',
				'category'   => 'science',
				'tags'       => array(),
				'type'       => 'news',
				'author'     => 'arjun',
				'hours_ago'  => 22,
				'views'      => 1600,
				'paragraphs' => array(
					__( 'Researchers isolated a compound from a Western Ghats soil sample that disrupted a drug-resistant staph strain in vitro, according to a preprint.', 'infy-news-os-core' ),
					__( 'Most such leads fail in animals. The team is applying for follow-on funding and has not licensed the molecule. We are reporting it because the method — pairing local field sampling with a public strain library — is the part worth copying.', 'infy-news-os-core' ),
				),
			),
			array(
				'slug'       => 'inos-demo-shipping-red-sea',
				'title'      => __( 'Insurers hike Red Sea premiums again as rerouting becomes the norm', 'infy-news-os-core' ),
				'kicker'     => __( 'Trade', 'infy-news-os-core' ),
				'dek'        => __( 'Asia–Europe schedules have quietly rebuilt around the Cape. Shoppers will notice in prices, not maps.', 'infy-news-os-core' ),
				'dateline'   => 'LONDON',
				'category'   => 'world',
				'tags'       => array( 'policy' ),
				'type'       => 'news',
				'author'     => 'priya',
				'hours_ago'  => 3,
				'views'      => 2400,
				'paragraphs' => array(
					__( 'War-risk premiums on Red Sea transits rose for a third month, brokers said, even as some carriers advertised “limited” returns to the route.', 'infy-news-os-core' ),
					__( 'The practical network has already moved. Container alliances rewrote rotations around southern Africa last year. That adds fuel, time, and carbon — costs that show up in toys and machine parts, not in a single headline.', 'infy-news-os-core' ),
				),
			),
			array(
				'slug'       => 'inos-demo-eu-ai-act',
				'title'      => __( 'Europe’s AI rules start to bite for Indian SaaS exporters', 'infy-news-os-core' ),
				'kicker'     => __( 'Regulation', 'infy-news-os-core' ),
				'dek'        => __( 'If your customer is in Frankfurt, the paperwork is now part of the product.', 'infy-news-os-core' ),
				'dateline'   => 'BRUSSELS',
				'category'   => 'world',
				'tags'       => array( 'ai', 'policy' ),
				'type'       => 'analysis',
				'author'     => 'priya',
				'hours_ago'  => 12,
				'views'      => 3100,
				'paragraphs' => array(
					__( 'High-risk provisions of the EU AI Act are no longer a conference slide. Procurement teams in Germany and the Netherlands have begun asking Indian software vendors for model cards, logging plans, and human-oversight diagrams.', 'infy-news-os-core' ),
					__( 'That is not protectionism in the old tariff sense. It is a new kind of non-tariff barrier: compliance capacity. Firms that already document systems for SOC 2 will adapt. Firms that ship “an API and a smile” will stall.', 'infy-news-os-core' ),
				),
			),
			array(
				'slug'       => 'inos-demo-pacific-cables',
				'title'      => __( 'A cut cable is now a diplomatic event', 'infy-news-os-core' ),
				'kicker'     => __( 'Infrastructure', 'infy-news-os-core' ),
				'dek'        => __( 'The internet was supposed to route around damage. Politics does not.', 'infy-news-os-core' ),
				'dateline'   => 'SINGAPORE',
				'category'   => 'world',
				'tags'       => array( 'cybersecurity' ),
				'type'       => 'news',
				'author'     => 'arjun',
				'hours_ago'  => 20,
				'views'      => 1900,
				'paragraphs' => array(
					__( 'Two Pacific cables went dark within a week. Operators speak carefully about anchors and geology. Foreign ministries speak less carefully about intent.', 'infy-news-os-core' ),
					__( 'Repair ships are few. Permits are slow. The outage is a reminder that “the cloud” is a slogan wrapped around wet, heavy, national infrastructure.', 'infy-news-os-core' ),
				),
			),
			array(
				'slug'       => 'inos-demo-election-deepfakes',
				'title'      => __( 'Election deepfakes got cheaper. Fact-checks did not get faster', 'infy-news-os-core' ),
				'kicker'     => __( 'Media', 'infy-news-os-core' ),
				'dek'        => __( 'The scarce resource is not detection. It is attention in the first hour.', 'infy-news-os-core' ),
				'dateline'   => 'NEW DELHI',
				'category'   => 'world',
				'tags'       => array( 'ai', 'policy' ),
				'type'       => 'explainer',
				'author'     => 'maya',
				'hours_ago'  => 26,
				'views'      => 2200,
				'paragraphs' => array(
					__( 'Cloned candidate audio now costs less than a dinner. Platforms label some of it. Voters see the clip first and the label later, if at all.', 'infy-news-os-core' ),
					__( 'Newsrooms cannot out-produce a meme factory. What they can do is pre-bunk the likely lies, keep a public log of authentic audio, and refuse to launder unlabeled clips as “the internet is saying.”', 'infy-news-os-core' ),
				),
			),
			array(
				'slug'       => 'inos-demo-festival-film',
				'title'      => __( 'A quiet Malayalam thriller just became the festival circuit’s surprise hit', 'infy-news-os-core' ),
				'kicker'     => __( 'Film', 'infy-news-os-core' ),
				'dek'        => __( 'No superheroes. A ferry. Two phone calls. Distributors are suddenly interested.', 'infy-news-os-core' ),
				'dateline'   => 'KOCHI',
				'category'   => 'culture',
				'tags'       => array(),
				'type'       => 'review',
				'author'     => 'maya',
				'hours_ago'  => 10,
				'views'      => 2800,
				'trending_pin' => 1,
				'paragraphs' => array(
					__( '“Night Jetty” runs 98 minutes and contains almost no score. That should have doomed it. Instead, programmers in Busan and Toronto have been whispering about it like a secret.', 'infy-news-os-core' ),
					__( 'The film is a procedural about a missing deckhand that becomes a portrait of gig work at sea. It trusts the audience. In a year of franchise noise, that feels radical.', 'infy-news-os-core' ),
					__( 'A streaming bidding war would miss the point and also, probably, happen anyway.', 'infy-news-os-core' ),
				),
			),
			array(
				'slug'       => 'inos-demo-game-studios',
				'title'      => __( 'Indian game studios are hiring writers. That is the story', 'infy-news-os-core' ),
				'kicker'     => __( 'Games', 'infy-news-os-core' ),
				'dek'        => __( 'For years the work was ports and ad playables. Narrative jobs mean the industry is growing up.', 'infy-news-os-core' ),
				'dateline'   => 'PUNE',
				'category'   => 'culture',
				'tags'       => array( 'startups' ),
				'type'       => 'news',
				'author'     => 'maya',
				'hours_ago'  => 15,
				'views'      => 1700,
				'paragraphs' => array(
					__( 'Three mid-size studios posted narrative designer roles this month — a job title that barely existed in local listings two years ago.', 'infy-news-os-core' ),
					__( 'The work is still commercial: live-service loops, seasonal events, licensed IP. But paying people to care about story is how an industry stops being only a services bench.', 'infy-news-os-core' ),
				),
			),
			array(
				'slug'       => 'inos-demo-cookbook',
				'title'      => __( 'The year’s best cookbook is secretly a reporting project', 'infy-news-os-core' ),
				'kicker'     => __( 'Books', 'infy-news-os-core' ),
				'dek'        => __( 'Recipes from mill towns, with wages and water rights in the headnotes.', 'infy-news-os-core' ),
				'dateline'   => 'CHENNAI',
				'category'   => 'culture',
				'tags'       => array(),
				'type'       => 'review',
				'author'     => 'maya',
				'hours_ago'  => 24,
				'views'      => 1400,
				'paragraphs' => array(
					__( 'Most cookbooks flatten place into flavor. This one keeps the mill, the shift schedule, and the reason a gravy thickened when pay was late.', 'infy-news-os-core' ),
					__( 'You can cook from it. You can also file it next to labor history. That combination is rarer than another air-fryer chapter.', 'infy-news-os-core' ),
				),
			),
			array(
				'slug'       => 'inos-demo-podcast-money',
				'title'      => __( 'When a culture podcast becomes a shopping channel', 'infy-news-os-core' ),
				'kicker'     => __( 'Media', 'infy-news-os-core' ),
				'dek'        => __( 'Affiliate links pay the bills. They also train the audience to distrust the host.', 'infy-news-os-core' ),
				'dateline'   => 'MUMBAI',
				'category'   => 'culture',
				'tags'       => array(),
				'type'       => 'analysis',
				'author'     => 'maya',
				'hours_ago'  => 30,
				'views'      => 1250,
				'sponsored'  => 0,
				'paragraphs' => array(
					__( 'Independent audio is a real business now. That is good. The tell is when every episode ends in a mattress.', 'infy-news-os-core' ),
					__( 'Disclosure is not a moral victory if the show is structurally an infomercial. Listeners are not fools. They just have fewer alternatives that pay researchers.', 'infy-news-os-core' ),
				),
			),
			array(
				'slug'       => 'inos-demo-open-source-duty',
				'title'      => __( 'Open source is not a charity. Treat maintainers like infrastructure', 'infy-news-os-core' ),
				'kicker'     => __( 'The argument', 'infy-news-os-core' ),
				'dek'        => __( 'If your company depends on a library, pay for the people who keep it from rotting.', 'infy-news-os-core' ),
				'dateline'   => 'BENGALURU',
				'category'   => 'opinion',
				'tags'       => array( 'ai', 'cybersecurity' ),
				'type'       => 'opinion',
				'author'     => 'priya',
				'hours_ago'  => 13,
				'views'      => 3600,
				'paragraphs' => array(
					__( 'Every few years a volunteer-maintained library breaks the internet. We act surprised. We should act like utilities regulators.', 'infy-news-os-core' ),
					__( 'This is not a plea for pity. It is a plea for invoices. Corporations that built fortunes on unpaid labor can fund retainers, security audits, and succession plans.', 'infy-news-os-core' ),
					__( 'Governments that talk about digital public goods should buy them the way they buy roads: boringly, annually, in the budget.', 'infy-news-os-core' ),
				),
			),
			array(
				'slug'       => 'inos-demo-school-phones',
				'title'      => __( 'Banning phones in school is easy. Teaching attention is the actual job', 'infy-news-os-core' ),
				'kicker'     => __( 'Education', 'infy-news-os-core' ),
				'dek'        => __( 'Lockers help. They are not a pedagogy.', 'infy-news-os-core' ),
				'dateline'   => 'BENGALURU',
				'category'   => 'opinion',
				'tags'       => array( 'policy' ),
				'type'       => 'opinion',
				'author'     => 'maya',
				'hours_ago'  => 19,
				'views'      => 2100,
				'paragraphs' => array(
					__( 'A phone-free campus is a reasonable default. Treating it as the whole solution is how we get another cycle of moral panic without better teaching.', 'infy-news-os-core' ),
					__( 'Students still need to learn how to search, doubt, and look up from a screen on purpose. That is curriculum, not confiscation.', 'infy-news-os-core' ),
				),
			),
			array(
				'slug'        => 'inos-demo-correction-sample',
				'title'       => __( 'City to trial 15-minute bus lanes on two corridors', 'infy-news-os-core' ),
				'kicker'      => __( 'Cities', 'infy-news-os-core' ),
				'dek'         => __( 'The pilot starts next month. It is not a congestion charge.', 'infy-news-os-core' ),
				'dateline'    => 'BENGALURU',
				'category'    => 'world',
				'tags'        => array( 'policy' ),
				'type'        => 'news',
				'author'      => 'arjun',
				'hours_ago'   => 28,
				'views'       => 900,
				'correction'  => __( 'An earlier version of this article misstated the pilot length as 50 kilometres. It is 15 kilometres across two corridors.', 'infy-news-os-core' ),
				'paragraphs'  => array(
					__( 'Bengaluru will trial dedicated bus lanes on two radial corridors for six months, the transport body said, aiming to cut peak travel times for public transit.', 'infy-news-os-core' ),
					__( 'Private-car groups called the plan “anti-motorist.” Transit riders called it overdue. Both can be true; the data from the trial will decide whether it spreads.', 'infy-news-os-core' ),
				),
			),
			array(
				'slug'            => 'inos-demo-sponsored-cloud',
				'title'           => __( 'How a mid-size retailer cut checkout latency without a rewrite', 'infy-news-os-core' ),
				'kicker'          => __( 'Partner story', 'infy-news-os-core' ),
				'dek'             => __( 'A labeled look at edge caching, paid for by a cloud company. The newsroom edited the copy.', 'infy-news-os-core' ),
				'dateline'        => 'HYDERABAD',
				'category'        => 'technology',
				'tags'            => array(),
				'type'            => 'news',
				'author'          => 'maya',
				'hours_ago'       => 32,
				'views'           => 800,
				'sponsored'       => 1,
				'sponsored_label' => __( 'Sponsored', 'infy-news-os-core' ),
				'paragraphs'      => array(
					__( 'This article is sponsored. A regional retailer moved session tokens closer to shoppers and reported faster checkouts during a festival sale, according to figures the company supplied and we could not independently audit.', 'infy-news-os-core' ),
					__( 'The architecture is not magic: cache what you can, keep payments in a tighter region, and measure p95, not averages. Treat vendor case studies as a starting point for questions, not as a benchmark.', 'infy-news-os-core' ),
				),
			),
			array(
				'slug'         => 'inos-demo-grid-water',
				'title'        => __( 'Data centres now compete with farmers for water', 'infy-news-os-core' ),
				'kicker'       => __( 'Climate', 'infy-news-os-core' ),
				'dek'          => __( 'Cooling a model is not free. The bill shows up in a reservoir.', 'infy-news-os-core' ),
				'dateline'     => 'HYDERABAD',
				'category'     => 'science',
				'tags'         => array( 'climate', 'energy', 'ai' ),
				'type'         => 'explainer',
				'author'       => 'arjun',
				'hours_ago'    => 8,
				'views'        => 4100,
				'homepage_pin' => 0,
				'inline_image' => 1,
				'blocks'       => array(
					array(
						'type' => 'heading',
						'text' => __( 'What “water-positive” actually means', 'infy-news-os-core' ),
					),
					array(
						'type' => 'p',
						'text' => __( 'Operators publish litres per kilowatt-hour as if the catchment were infinite. In a drought year the same litre is a crop, a household, and a cooling tower.', 'infy-news-os-core' ),
					),
					array(
						'type' => 'quote',
						'text' => __( 'We can recycle on site. We cannot invent a river.', 'infy-news-os-core' ),
						'cite' => __( 'Hydrologist, demo briefing', 'infy-news-os-core' ),
					),
					array(
						'type'  => 'list',
						'items' => array(
							__( 'Ask for peak-day withdrawals, not annual averages.', 'infy-news-os-core' ),
							__( 'Put recycled-water pledges in the environmental clearance.', 'infy-news-os-core' ),
							__( 'Publish the catchment, not a global offset.', 'infy-news-os-core' ),
						),
					),
					array(
						'type' => 'p',
						'text' => __( 'This is demo reporting. Treat the numbers as a layout sample, not a dataset.', 'infy-news-os-core' ),
					),
				),
			),
			array(
				'slug'         => 'inos-demo-clinic-records',
				'title'        => __( 'Hospitals digitized files. Patients still carry printouts', 'infy-news-os-core' ),
				'kicker'       => __( 'Health', 'infy-news-os-core' ),
				'dek'          => __( 'Interoperability is a slogan until two clinics share a lab result without a PDF.', 'infy-news-os-core' ),
				'dateline'     => 'CHENNAI',
				'category'     => 'science',
				'tags'         => array( 'health', 'policy' ),
				'type'         => 'news',
				'author'       => 'maya',
				'hours_ago'    => 11,
				'views'        => 1750,
				'inline_image' => 1,
				'paragraphs'   => array(
					__( 'A national health ID was supposed to end the folder of scans. Front desks still ask for last month’s printout because the vendor APIs do not talk.', 'infy-news-os-core' ),
					__( 'The failure is not “digital literacy.” It is procurement that bought portals instead of records.', 'infy-news-os-core' ),
				),
			),
			array(
				'slug'       => 'inos-demo-battery-rush',
				'title'      => __( 'The battery rush is a mining story first', 'infy-news-os-core' ),
				'kicker'     => __( 'Energy', 'infy-news-os-core' ),
				'dek'        => __( 'Gigafactories make better slides than lithium brine does.', 'infy-news-os-core' ),
				'dateline'   => 'BENGALURU',
				'category'   => 'business',
				'tags'       => array( 'energy', 'startups' ),
				'type'       => 'analysis',
				'author'     => 'priya',
				'hours_ago'  => 16,
				'views'      => 2400,
				'paragraphs' => array(
					__( 'Every deck has a gigafactory. Few have a named mine, a water table, or a community agreement.', 'infy-news-os-core' ),
					__( 'If the newsroom only covers the ribbon-cutting, it is covering marketing.', 'infy-news-os-core' ),
				),
			),
			array(
				'slug'       => 'inos-demo-city-heat',
				'title'      => __( 'Night-time heat is the inequality the AQI missed', 'infy-news-os-core' ),
				'kicker'     => __( 'Cities', 'infy-news-os-core' ),
				'dek'        => __( 'Shade, not slogans, decides who sleeps.', 'infy-news-os-core' ),
				'dateline'   => 'DELHI',
				'category'   => 'world',
				'tags'       => array( 'climate', 'policy' ),
				'type'       => 'news',
				'author'     => 'arjun',
				'hours_ago'  => 22,
				'views'      => 3100,
				'trending_pin' => 1,
				'blocks'     => array(
					array(
						'type' => 'p',
						'text' => __( 'Air-quality indexes made the smog visible. Heat at 2 a.m. still hides in rooms without a tree outside the window.', 'infy-news-os-core' ),
					),
					array(
						'type' => 'heading',
						'text' => __( 'What we measured (demo)', 'infy-news-os-core' ),
					),
					array(
						'type'  => 'list',
						'items' => array(
							__( 'Canopy cover on two bus corridors.', 'infy-news-os-core' ),
							__( 'Night temperature at balcony height, not airport official.', 'infy-news-os-core' ),
							__( 'Who has a legal claim to the shade tree.', 'infy-news-os-core' ),
						),
					),
					array(
						'type' => 'quote',
						'text' => __( 'We painted a roof white and called it a climate plan. The lane is still asphalt.', 'infy-news-os-core' ),
						'cite' => __( 'Municipal engineer, off record (demo)', 'infy-news-os-core' ),
					),
				),
			),
		);
	}

	/**
	 * Live blog + updates.
	 *
	 * @return array<string, mixed>
	 */
	public static function live_blog() {
		$blogs = self::live_blogs();
		return $blogs[0];
	}

	/**
	 * All demo live blogs.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function live_blogs() {
		return array(
			array(
				'slug'     => 'inos-demo-live-chip-summit',
				'title'    => __( 'Live: India Semiconductor Summit — day one', 'infy-news-os-core' ),
				'dek'      => __( 'Ministers, foundry executives, and the water question. We are in the room.', 'infy-news-os-core' ),
				'kicker'   => __( 'Live', 'infy-news-os-core' ),
				'dateline' => 'GANDHINAGAR',
				'author'   => 'priya',
				'category' => 'technology',
				'type'     => 'live',
				'color'    => '#0b3d5c',
				'updates'  => array(
					array(
						'title'     => __( 'Doors open; security queue spills onto the plaza', 'infy-news-os-core' ),
						'hours_ago' => 6,
						'body'      => __( 'Badge printers jammed for twenty minutes. Delegates are being asked to use the overflow hall for the first keynote.', 'infy-news-os-core' ),
					),
					array(
						'title'     => __( 'Minister: “Clearance is not a chip”', 'infy-news-os-core' ),
						'hours_ago' => 5,
						'body'      => __( 'The line landed. The speech otherwise stuck to investment figures already in the brochure.', 'infy-news-os-core' ),
					),
					array(
						'title'     => __( 'Foundry CEO dodges the EUV timeline', 'infy-news-os-core' ),
						'hours_ago' => 4,
						'body'      => __( 'Asked when the first wafer would be commercially useful, the answer was “when the process is ready.” Analysts in the press row wrote that down as “not this decade’s election.”', 'infy-news-os-core' ),
					),
					array(
						'title'     => __( 'Farmers’ group holds a briefing across the road', 'infy-news-os-core' ),
						'hours_ago' => 3,
						'body'      => __( 'They want the recycled-water pledge in the environmental clearance, not in a slide deck. We will publish the documents if we get them.', 'infy-news-os-core' ),
					),
					array(
						'title'     => __( 'Evening: no joint communiqué', 'infy-news-os-core' ),
						'hours_ago' => 1,
						'body'      => __( 'Talks continue tomorrow. For today, the news is the clearance itself — and how little of the engineering is settled.', 'infy-news-os-core' ),
					),
				),
			),
			array(
				'slug'     => 'inos-demo-live-heat-alert',
				'title'    => __( 'Live: Heat alert — night temperatures, hospitals, and shade', 'infy-news-os-core' ),
				'dek'      => __( 'A city-level heat desk. Updates as wards report, not as the airport does.', 'infy-news-os-core' ),
				'kicker'   => __( 'Live', 'infy-news-os-core' ),
				'dateline' => 'DELHI',
				'author'   => 'arjun',
				'category' => 'science',
				'type'     => 'live',
				'color'    => '#3d2a0b',
				'updates'  => array(
					array(
						'title'     => __( 'Municipal dashboard shows 41.2°C at 4 p.m.', 'infy-news-os-core' ),
						'hours_ago' => 5,
						'body'      => __( 'That is the official station. Balcony thermometers in two east-side colonies were already higher at 2 p.m., residents told us.', 'infy-news-os-core' ),
					),
					array(
						'title'     => __( 'Three public hospitals add overflow cots', 'infy-news-os-core' ),
						'hours_ago' => 3,
						'body'      => __( 'Administrators would not give heat-stroke counts on the record. A duty doctor said the pattern is “nights that do not cool.”', 'infy-news-os-core' ),
					),
					array(
						'title'     => __( 'Cooling centres: six open, two without water', 'infy-news-os-core' ),
						'hours_ago' => 2,
						'body'      => __( 'We visited two. One had a working cooler and a queue. One had a locked tank and a handwritten note.', 'infy-news-os-core' ),
					),
					array(
						'title'     => __( 'Night: still 34°C at 11 p.m. in the lane we are tracking', 'infy-news-os-core' ),
						'hours_ago' => 1,
						'body'      => __( 'Coverage continues. This is demo live coverage, not a real weather desk.', 'infy-news-os-core' ),
					),
				),
			),
		);
	}

	/**
	 * Official Web Stories plugin demo items (portrait AMP stories).
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function web_stories() {
		return array(
			array(
				'slug'  => 'inos-demo-story-chips',
				'title' => __( 'Chips: what “clearance” actually means', 'infy-news-os-core' ),
				'dek'   => __( 'A foundry is not a press release.', 'infy-news-os-core' ),
				'color' => '#0b3d5c',
				'pages' => array(
					array(
						'kicker' => __( 'Technology', 'infy-news-os-core' ),
						'text'   => __( 'A licence to build is not a wafer. Here is the gap the brochure skips.', 'infy-news-os-core' ),
					),
					array(
						'kicker' => __( 'Water', 'infy-news-os-core' ),
						'text'   => __( 'Ask for peak-day withdrawals. Annual averages hide a drought.', 'infy-news-os-core' ),
					),
					array(
						'kicker' => __( 'Talent', 'infy-news-os-core' ),
						'text'   => __( 'Process engineers are not interchangeable with app developers.', 'infy-news-os-core' ),
					),
				),
			),
			array(
				'slug'  => 'inos-demo-story-ai-newsroom',
				'title' => __( 'AI in the newsroom, without the hype reel', 'infy-news-os-core' ),
				'dek'   => __( 'Transcription is a tool. Attribution is still a person.', 'infy-news-os-core' ),
				'color' => '#1a3d2f',
				'pages' => array(
					array(
						'kicker' => __( 'Newsroom', 'infy-news-os-core' ),
						'text'   => __( 'We use models to draft transcripts. We do not let them invent quotes.', 'infy-news-os-core' ),
					),
					array(
						'kicker' => __( 'Readers', 'infy-news-os-core' ),
						'text'   => __( 'If a sentence cannot be sourced, it does not ship — generated or not.', 'infy-news-os-core' ),
					),
				),
			),
			array(
				'slug'  => 'inos-demo-story-heat',
				'title' => __( 'Why night heat is the story', 'infy-news-os-core' ),
				'dek'   => __( 'The airport number is not the lane.', 'infy-news-os-core' ),
				'color' => '#3d2a0b',
				'pages' => array(
					array(
						'kicker' => __( 'Climate', 'infy-news-os-core' ),
						'text'   => __( 'Days make headlines. Nights that do not cool fill wards.', 'infy-news-os-core' ),
					),
					array(
						'kicker' => __( 'Shade', 'infy-news-os-core' ),
						'text'   => __( 'A tree is infrastructure. Paint is a press photo.', 'infy-news-os-core' ),
					),
					array(
						'kicker' => __( 'Policy', 'infy-news-os-core' ),
						'text'   => __( 'Cooling centres without water are a poster, not a plan.', 'infy-news-os-core' ),
					),
				),
			),
			array(
				'slug'  => 'inos-demo-story-markets',
				'title' => __( 'Markets: the listing that was a warning', 'infy-news-os-core' ),
				'dek'   => __( 'Retail piled in. The lock-up will tell.', 'infy-news-os-core' ),
				'color' => '#1a3d2f',
				'pages' => array(
					array(
						'kicker' => __( 'Business', 'infy-news-os-core' ),
						'text'   => __( 'A packed book is not due diligence. Read the related-party notes.', 'infy-news-os-core' ),
					),
					array(
						'kicker' => __( 'Risk', 'infy-news-os-core' ),
						'text'   => __( 'When lock-ups expire, the story is who is allowed to sell — not the listing-day selfie.', 'infy-news-os-core' ),
					),
				),
			),
			array(
				'slug'  => 'inos-demo-story-festival',
				'title' => __( 'A quiet thriller, three frames', 'infy-news-os-core' ),
				'dek'   => __( 'No superheroes. A ferry. Two phone calls.', 'infy-news-os-core' ),
				'color' => '#5c2a1a',
				'pages' => array(
					array(
						'kicker' => __( 'Film', 'infy-news-os-core' ),
						'text'   => __( '“Night Jetty” trusts silence. That is the surprise, not a twist.', 'infy-news-os-core' ),
					),
					array(
						'kicker' => __( 'Craft', 'infy-news-os-core' ),
						'text'   => __( 'Almost no score. The engine is the wait between two calls.', 'infy-news-os-core' ),
					),
				),
			),
			array(
				'slug'  => 'inos-demo-story-open-source',
				'title' => __( 'Pay the maintainers', 'infy-news-os-core' ),
				'dek'   => __( 'Open source is infrastructure. Invoice it.', 'infy-news-os-core' ),
				'color' => '#1a1a1a',
				'pages' => array(
					array(
						'kicker' => __( 'Opinion', 'infy-news-os-core' ),
						'text'   => __( 'If your company depends on a library, fund the people who keep it from rotting.', 'infy-news-os-core' ),
					),
					array(
						'kicker' => __( 'How', 'infy-news-os-core' ),
						'text'   => __( 'Retainers, audits, succession — not a one-off conference tote.', 'infy-news-os-core' ),
					),
				),
			),
		);
	}

	/**
	 * Extra media-library stills (not featured on a story).
	 *
	 * @return array<int, array<string, string>>
	 */
	public static function extra_media() {
		return array(
			array(
				'title' => __( 'Newsroom desk — demo still', 'infy-news-os-core' ),
				'color' => '#0b3d5c',
			),
			array(
				'title' => __( 'Server hall — demo still', 'infy-news-os-core' ),
				'color' => '#1a1a1a',
			),
			array(
				'title' => __( 'City at dusk — demo still', 'infy-news-os-core' ),
				'color' => '#2c1a3d',
			),
			array(
				'title' => __( 'Field reporting kit — demo still', 'infy-news-os-core' ),
				'color' => '#3d2a0b',
			),
		);
	}

	/**
	 * Sample comments keyed by story slug.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function comments() {
		return array(
			array(
				'slug'    => 'inos-demo-grid-water',
				'author'  => 'Demo reader',
				'email'   => 'demo.reader@example.com',
				'content' => __( 'Useful context — especially the part about peak-day withdrawals. (This is a demo comment.)', 'infy-news-os-core' ),
			),
			array(
				'slug'    => 'inos-demo-grid-water',
				'author'  => 'Demo editor',
				'email'   => 'demo.editor@example.com',
				'content' => __( 'We should follow the environmental clearance, not the slide. (Demo reply.)', 'infy-news-os-core' ),
			),
			array(
				'slug'    => 'inos-demo-school-phones',
				'author'  => 'Demo parent',
				'email'   => 'demo.parent@example.com',
				'content' => __( 'Lockers help the first week. Then the pedagogy has to show up. (Demo comment.)', 'infy-news-os-core' ),
			),
			array(
				'slug'    => 'inos-demo-city-heat',
				'author'  => 'Demo reader',
				'email'   => 'demo.reader@example.com',
				'content' => __( 'The night number is the one my street actually feels. (Demo comment.)', 'infy-news-os-core' ),
			),
		);
	}

	/**
	 * Demo newsletter rows.
	 *
	 * @return string[]
	 */
	public static function subscribers() {
		return array(
			'demo.reader@example.com',
			'demo.editor@example.com',
			'demo.press@example.com',
			'demo.parent@example.com',
		);
	}
}
