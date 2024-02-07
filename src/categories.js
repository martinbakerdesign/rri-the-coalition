const regions = [
    {
        name: 'Africa',
        img: '',
        description: [
            "The majority of Sub-Saharan Africa is traditionally owned by local peoples, yet the recognition of their rights lags behind other regions. Our Coalition's efforts in Africa have ensured the implementation of land ownership and community rights laws, as well as fostering dialogues for community-led economic models."
        ]
    },
    {
        name: 'Asia',
        img: '',
        description: [
            "The South and Southeast Asia region exhibits both high rates of deforestation and an immense population of Indigenous Peoples and local communities. Coalition members in Asia are Indigenous, local community, and civil society organizations working to secure customary land rights under the law, develop smarter regulations, and enhance their economic prosperity."
        ]
    },
    {
        name: 'Latin America',
        img: '',
        description: [
            "The majority of Sub-Saharan Africa is traditionally owned by local peoples, yet the recognition of their rights lags behind other regions. Our Coalition's efforts in Africa have ensured the implementation of land ownership and community rights laws, as well as fostering dialogues for community-led economic models."
        ]
    },
]
const topics = [
    {
        name: 'Gender Justice',
        img: '',
        description: [
            "Our coalition empowers Indigenous, Afro-descendant, and community women by scaling up efforts to secure their tenure rights, amplify their voices, and enhance their leadership within the realm of community lands and forests. By addressing gender disparities, our members seek to create a more equitable and inclusive world where these women have a say in the governance of their territories as well as in decisions on climate and conservation action that impact their rights."
        ]
    },
    {
        name: 'Climate & Conservation',
        img: '',
        description: [
            "Our coalition members are involved in research, advocacy, and convenings that promote the inclusion of community rights in international climate and conservation instruments as a safeguarding measure as well as means of protecting the planet. coalition members routinely contribute to data collection and peer review of research that promotes the connection between secure community land rights and improved climate and conservation outcomes. They also support a wide range of projects that help communities sustainably govern and conserve their territories in accordance with their customary knowledge. This can range from supporting community based conservation initiatives to advocating for their right to self-determined conservation."
        ]
    },
    {
        name: 'Private Sector Engagement',
        img: '',
        description: [
            "Our coalition members catalyze private sector action to secure community land rights in land based investments and operations. Examples of their interventions with the private sector include convening dialogues between corporate actors and investors and community leaders, and conducting analyses that guide these actors on incorporating respect for community land rights in land based projects as well as the financial and operational risks of insecure tenure."
        ]
    },
    {
        name: 'Livelihoods',
        img: '',
        description: [
            "RRI member organizations are working with a broad array of actors to advance community-led economic and livelihood approaches, as well as producing operational and policy guidance to scale up government and private sector actors to support these approaches."
        ]
    },
    {
        name: 'Monitoring and Tracking Community Tenure',
        img: '',
        description: [
            "RRI’s members help track and monitor legal recognition of Indigenous Peoples’, Afro-descendant Peoples’, and local communities’ rights across the world—particularly of the women within them—to forests, land, and natural resources. They work closely with RRI’s research team to provide crucial data and peer reviews for its four databases that examine various aspects of community rights recognition globally and regionally."
        ]
    },
    {
        name: 'Youth & Intergenerational Leadership',
        img: '',
        description: [
            "Our coalition has identified building Indigenous and local community youth’s capacity for taking on leadership roles in their organizations and on global platforms as a key priority for coming years. Recent coalition contributions to this area have included research to highlight youth aspirations and challenges within their communities, and convenings for regional youth leaders to promote knowledge sharing and solidarity."
        ]
    },
    {
        name: 'Environmental & Human Rights Defenders',
        img: '',
        description: [
            "Coalition contributions include data collection and analysis highlighting criminalization of defenders on national and regional levels."
        ]
    },
    {
        name: 'Water',
        img: '',
        description: [
            "Several RRI members are engaged in research and analysis to establish the status of communities' collective rights to water resources, and the link between these rights and various development outcomes."
        ]
    },
]
const expertise = [
    {
        name: 'Research',
        img: '',
        description: [
            "Our coalition members’ research spans tracking community tenure rights to analyzing the various thematic issues that affect them, including but not limited to gender justice, community livelihoods, private sector investments, criminalization of Indigenous and local community defenders, climate, conservation, and more recently youth. "
        ]
    },
    {
        name: 'Policy  Legal Advocacy',
        img: '',
        description: [
            "RRI's policy and legal advocacy work takes place through its research, convening of dialogues between unlikely allies across various sectors, and facilitation of global, regional, and national level networks of community rights advocates. Through their national level networks RRI coalition members engage with governments to affect and implement legislation that supports community rights. RRI's global advocacy includes influencing international climate and conservation instruments to incorporate community rights as a vital component."
        ]
    },
    {
        name: 'Legal Support',
        img: '',
        description: [
            "Several of RRI's member organizations support legal training for community organizations to enable them to advocate for legislative reforms in their constituencies. They also facilitate dialogue between rightsholders and policymakers to build the latter's understanding of the importance of community tenure."
        ]
    },
    {
        name: 'Capacity Building & Knowledge Exchange',
        img: '',
        description: [
            "Coalition members’ capacity building support ranges from building communities' legal advocacy skills to supporting them in mapping their lands and contributing to research establishing the link between secure rights and various development goals. They also suppors targeted leadership and advocacy training for community women and youth. "
        ]
    },
    {
        name: 'Campaigns Mobilization',
        img: '',
        description: [
            "The coalition supports national and global advocacy campaigns led by rightsholders across the global South to secure rights, protest criminalization of land rights defenders and raise their voices on global platforms, and prevent community displacement and rollback of rights due to economic policies and commercial development."
        ]
    },
    {
        name: 'Communication & Storytelling',
        img: '',
        description: [
            "Our coalition promotes and supports Indigenous-led journalism and creation of communicators’ networks to ensure communities’ voices reach broader audiences. RRI members have used a variety of media to convey stories of impact and conflict – ranging from video, radio, community theater, and social media to organizing traditional media site visits to community territories."
        ]
    },
    {
        name: 'Mapping & Spatial Data',
        img: '',
        description: [
            "Our coalition members help communities with mapping of historic tenure claims and have developed due diligence tools for them to work with governments, suppliers and outgrowers. Coalition members have also conducted regional knowledge exchanges to share successes and lessons from their experiences with mapping."
        ]
    },
    {
        name: 'Funding Mechanisms',
        img: '',
        description: []
    },
]
let regionId = 0;
let topicId = 0;
let expertiseId = 0;

const regionsContainer = document.querySelector('#regions .accordion');
regionsContainer.innerHTML = '';
for (let region of regions) {
    let id = getRegionId()
    regionsContainer?.appendChild(createPanel(region, 'regions', id));
}

const topicsContainer = document.querySelector('#topics .accordion');
topicsContainer.innerHTML = '';
for (let topic of topics) {
    let id = getTopicId()
    topicsContainer?.appendChild(createPanel(topic, 'topics', id));
}


const expertiseContainer = document.querySelector('#expertise .accordion');
expertiseContainer.innerHTML = '';
for (let e of expertise) {
    let id = getExpertiseId()
    expertiseContainer?.appendChild(createPanel(e, 'expertise', 'id'));
}


function getRegionId () {
    regionId++;
    return regionId;
}
function getTopicId () {
    topicId++;
    return topicId;
}
function getExpertiseId () {
    expertiseId++;
    return expertiseId;
}

function createPanel (category, categoryId, id) {
    const panel = createElement('div',{className: 'categories__panel'})

    const trigger = createElement('button',{className: 'accordion__trigger'});
    accordionTrigger(trigger, categoryId, id);
    
    const thumb = createElement('div',{className: 'categories__img'});
    const thumbImg = createElement('img',{src:category.img, alt: category.name})
    thumb.appendChild(thumbImg);
    
    const label = createElement('h4', {id:`${categoryId}__name--${id}`,className: 'categories__label'});
    label.textContent = category.name;
    
    trigger.appendChild(thumb);
    trigger.appendChild(label);

    const content = createElement('div',{className: 'accordion__content'});
    accordionContent(content, categoryId, id)
    const paras = category.description.map(p => createElement('p',{textContent: p}));
    paras.forEach(p => content.appendChild(p));

    panel.appendChild(trigger);
    panel.appendChild(content);
    
    return panel;
}

function createElement (type = 'div', props = {})  {
    const el = document.createElement(type);
    props && (
        Object.entries(props).forEach(([key, value]) => (el[key] = value))
    );

    return el;
}

function accordionTrigger (el, categoryId, id) {
    el.setAttribute('aria-controls',`${categoryId}__content--${id}`);
    el.setAttribute('aria-expanded','false');
    el.id = `${categoryId}__trigger--${id}`;
}

function accordionContent (el, categoryId, id) {
    el.id = `${categoryId}__content--${id}`;
    el.setAttribute('aria-lablledby',`${categoryId}__name--${id}`);
    el.setAttribute('hidden','')
    el.setAttribute('role','region')
}