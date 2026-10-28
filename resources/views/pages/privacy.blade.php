<x-layouts.site title="Privacy notice" description="How Glennon Brothers handles the details sent through this website, under the GDPR, the Irish Data Protection Acts and UK GDPR.">
    <x-page-header photo="windymains-tank" title="Privacy notice" kicker="Privacy" lead="What we keep when you ask for a quote, send a timber frame enquiry, book a yard slot, apply for a job or send a message, and why." />
    <section class="wrap prose-copy measure mt-12 md:mt-16">
        <p>This website is run by {{ gb('name') }}, {{ gb('address.street') }}, {{ gb('address.city') }} {{ gb('address.postcode') }}, Ireland, which is the controller of the personal data sent through it. We handle it under the General Data Protection Regulation (GDPR) and the Data Protection Acts 1988 to 2018. Where you deal with our Scottish plants (Windymains Timber Ltd and Glennon Brothers (UK) Ltd), the UK GDPR and the Data Protection Act 2018 also apply. Questions: <a href="mailto:{{ gb('email') }}">{{ gb('email') }}</a> or {{ format_phone(gb('phone')) }}.</p>
        <h2>What we collect</h2>
        <ul>
            <li><strong>Trade quotes:</strong> your company, name, contact details, account number if you have one, the products and quantities, delivery address and any cutting list you attach.</li>
            <li><strong>Timber frame enquiries (DTE):</strong> your name, contact details, the site address, county and Eircode, planning position, start month and the drawings you upload.</li>
            <li><strong>Haulier bookings:</strong> the haulier, driver name, vehicle registration, contact details and the slot you ask for.</li>
            <li id="applicants"><strong>Job applications:</strong> your name, phone, email, the town you live in, whether you have the right to work, experience, licences, qualifications and CV if you attach one.</li>
            <li><strong>Messages:</strong> your name, contact details and message.</li>
        </ul>
        <h2>Why, and on what basis</h2>
        <p>We use quotes, enquiries, bookings and messages to answer you and, where you go ahead, to take steps towards a contract with you (GDPR Article 6(1)(b)). We use job applications to consider you for work, with your consent, which you can withdraw at any time (Article 6(1)(a)). We do not sell your details or use them for marketing.</p>
        <h2>Who sees it</h2>
        <p>Each kind of submission is seen only by the staff who handle it: the Ireland or UK sales team for quotes and bookings, the DTE team for timber frame enquiries, human resources and the plant manager for applications, and the site administrator. Details sent about a Scottish plant are handled by staff of the group in Ireland and the UK; transfers between the two rely on the EU adequacy decision for the UK.</p>
        <h2>Where it is kept</h2>
        <p>Uploads are stored on a private part of our web server that cannot be reached from the internet; staff open them after signing in. The private link we email to timber frame customers expires after {{ \App\Models\FrameEnquiry::LINK_DAYS }} days and can be withdrawn.</p>
        <h2>How long</h2>
        <ul>
            <li>Quotes, enquiries and bookings that do not lead to an order: up to 2 years.</li>
            <li>Records linked to an order: for as long as tax and company law require.</li>
            <li>Job applications: up to 12 months, unless you ask us to delete yours sooner.</li>
        </ul>
        <h2>Your rights</h2>
        <p>You can ask to see, correct, delete or move the details we hold about you, or object to how we use them, by emailing <a href="mailto:{{ gb('email') }}">{{ gb('email') }}</a>. Quote your reference number if you have one. If you are unhappy with our answer, you can complain to the Data Protection Commission (<a href="https://www.dataprotection.ie" rel="noopener">dataprotection.ie</a>) or, for our Scottish plants, the Information Commissioner's Office (<a href="https://ico.org.uk" rel="noopener">ico.org.uk</a>).</p>
        <h2>Cookies</h2>
        <p>The site uses one strictly necessary session cookie to keep forms secure, and your browser remembers whether you chose light or dark mode. There is no advertising or tracking, so there is no cookie banner.</p>
        <p class="hint">Last updated {{ now()->format('F Y') }}. To be reviewed by the group before launch.</p>
    </section>
</x-layouts.site>
