const urlBase = 'http://localhost:8000/LAMPAPI'; // Change to localhost for frontend testing
const extension = 'php';

let userId = 0;
let firstName = "";
let lastName = "";

function validateLoginFields() {
    let valid = true;
    const nameInput = document.getElementById('loginName');
    const passInput = document.getElementById('loginPassword');

    // Remove previous error styling
    nameInput.classList.remove('input-error');
    passInput.classList.remove('input-error');

    if (!nameInput.value.trim()) {
        nameInput.classList.add('input-error');
        valid = false;
    }
    if (!passInput.value.trim()) {
        passInput.classList.add('input-error');
        valid = false;
    }
    return valid;
}

async function validateAndRegister() {
    const fields = [
        'registerName',
        'registerEmail',
        'registerPhone',
        'registerPassword'
    ];
    let allFilled = true;

    fields.forEach(id => {
        const input = document.getElementById(id);
        if (!input.value.trim()) {
            input.classList.add('input-error');
            allFilled = false;
        } else {
            input.classList.remove('input-error');
        }
    });

    if (!allFilled) return;

    const name = document.getElementById('registerName').value.trim();
    const email = document.getElementById('registerEmail').value.trim();
    const phone = document.getElementById('registerPhone').value.trim();
    const password = document.getElementById('registerPassword').value.trim();
    const creationDate = new Date().toISOString();

    const userData = { name, email, phone, password, creationDate };
    const jsonPayload = JSON.stringify(userData);

    const url = urlBase + '/Register.' + extension;

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: jsonPayload
        });

        if (response.ok) {
            window.location.href = 'login.html';
        }
    } catch (err) {
    }
}

function doLogin()
{
	userId = 0;
	firstName = "";
	lastName = "";
	
	let login = document.getElementById("loginName").value;
	let password = document.getElementById("loginPassword").value;
	
	document.getElementById("loginResult").innerHTML = "";

	// For local frontend testing - mock the response
	if (urlBase.includes('localhost') || urlBase.includes('127.0.0.1')) {
		// Mock successful login for testing
		setTimeout(() => {
			userId = 1;
			firstName = "Test";
			lastName = "User";
			saveCookie();
			window.location.href = "contacts.html";
		}, 500);
		return;
	}

	let tmp = {login:login,password:password};
	let jsonPayload = JSON.stringify( tmp );
	
	let url = urlBase + '/Login.' + extension;

	let xhr = new XMLHttpRequest();
	xhr.open("POST", url, true);
	xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
	try
	{
		xhr.onreadystatechange = function() 
		{
			if (this.readyState == 4 && this.status == 200) 
			{
				let jsonObject = JSON.parse( xhr.responseText );
				userId = jsonObject.id;
		
				if( userId < 1 )
				{		
					document.getElementById("loginResult").innerHTML = "User/Password combination incorrect";
					return;
				}
		
				firstName = jsonObject.firstName;
				lastName = jsonObject.lastName;

				saveCookie();
	
				window.location.href = "contacts.html";
			}
		};
		xhr.send(jsonPayload);
	}
	catch(err)
	{
		document.getElementById("loginResult").innerHTML = err.message;
	}

}

function saveCookie()
{
	let minutes = 20;
	let date = new Date();
	date.setTime(date.getTime()+(minutes*60*1000));	
	document.cookie = "firstName=" + firstName + ",lastName=" + lastName + ",userId=" + userId + ";expires=" + date.toGMTString();
}

function readCookie()
{
	userId = -1;
	let data = document.cookie;
	let splits = data.split(",");
	for(var i = 0; i < splits.length; i++) 
	{
		let thisOne = splits[i].trim();
		let tokens = thisOne.split("=");
		if( tokens[0] == "firstName" )
		{
			firstName = tokens[1];
		}
		else if( tokens[0] == "lastName" )
		{
			lastName = tokens[1];
		}
		else if( tokens[0] == "userId" )
		{
			userId = parseInt( tokens[1].trim() );
		}
	}
	
	if( userId < 0 )
	{
		window.location.href = "index.html";
	}
	else
	{
//		document.getElementById("userName").innerHTML = "Logged in as " + firstName + " " + lastName;
	}
}

function doLogout()
{
	userId = 0;
	firstName = "";
	lastName = "";
	document.cookie = "firstName= ; expires = Thu, 01 Jan 1970 00:00:00 GMT";
	window.location.href = "index.html";
}

function addColor()
{
	let newColor = document.getElementById("colorText").value;
	document.getElementById("colorAddResult").innerHTML = "";

	let tmp = {color:newColor,userId:userId};
	let jsonPayload = JSON.stringify( tmp );

	let url = urlBase + '/AddColor.' + extension;
	
	let xhr = new XMLHttpRequest();
	xhr.open("POST", url, true);
	xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
	try
	{
		xhr.onreadystatechange = function() 
		{
			if (this.readyState == 4 && this.status == 200) 
			{
				document.getElementById("colorAddResult").innerHTML = "Color has been added";
			}
		};
		xhr.send(jsonPayload);
	}
	catch(err)
	{
		document.getElementById("colorAddResult").innerHTML = err.message;
	}
	
}

function searchColor()
{
	let srch = document.getElementById("searchText").value;
	document.getElementById("colorSearchResult").innerHTML = "";
	
	let colorList = "";

	let tmp = {search:srch,userId:userId};
	let jsonPayload = JSON.stringify( tmp );

	let url = urlBase + '/SearchColors.' + extension;
	
	let xhr = new XMLHttpRequest();
	xhr.open("POST", url, true);
	xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
	try
	{
		xhr.onreadystatechange = function() 
		{
			if (this.readyState == 4 && this.status == 200) 
			{
				document.getElementById("colorSearchResult").innerHTML = "Color(s) has been retrieved";
				let jsonObject = JSON.parse( xhr.responseText );
				
				for( let i=0; i<jsonObject.results.length; i++ )
				{
					colorList += jsonObject.results[i];
					if( i < jsonObject.results.length - 1 )
					{
						colorList += "<br />\r\n";
					}
				}
				
				document.getElementsByTagName("p")[0].innerHTML = colorList;
			}
		};
		xhr.send(jsonPayload);
	}
	catch(err)
	{
		document.getElementById("colorSearchResult").innerHTML = err.message;
	}
	
}

// -------------------- Contacts Page (frontend-only mock) --------------------

function debounce(func, wait)
{
	let timeoutId;
	return function() {
		const context = this;
		const args = arguments;
		clearTimeout(timeoutId);
		timeoutId = setTimeout(function() { func.apply(context, args); }, wait);
	};
}

const mockContacts = [
	{ id: 1, name: "Ada Lovelace", email: "ada@algorithms.io", phone: "555-1010" },
	{ id: 2, name: "Alan Turing", email: "alan@computing.org", phone: "555-2020" },
	{ id: 3, name: "Grace Hopper", email: "grace@navy.mil", phone: "555-3030" },
	{ id: 4, name: "Margaret Hamilton", email: "margaret@apollo.nasa", phone: "555-4040" }
];

function renderContacts(list)
{
	var container = document.getElementById('contactsList');
	var status = document.getElementById('contactsStatus');
	if (!container) { return; }

	if (!list || list.length === 0)
	{
		container.innerHTML = '<div style="text-align:center;">No contacts found.</div>';
		if (status) { status.style.display = 'none'; }
		return;
	}

	var html = '<table style="width:100%;border-collapse:separate;border-spacing:0 8px;">' +
		'<thead><tr><th style="text-align:left;">Name</th><th style="text-align:left;">Email</th><th style="text-align:left;">Phone</th></tr></thead><tbody>';
	for (var i = 0; i < list.length; i++)
	{
		var c = list[i];
		html += '<tr style="background:rgba(0,0,0,0.5);"><td style="padding:10px 12px;">' + c.name + '</td>' +
			'<td style="padding:10px 12px;">' + c.email + '</td>' +
			'<td style="padding:10px 12px;">' + c.phone + '</td></tr>';
	}
	html += '</tbody></table>';
	container.innerHTML = html;
	if (status) { status.style.display = 'none'; }
}

function initContactsPage()
{
	var input = document.getElementById('contactSearch');
	var status = document.getElementById('contactsStatus');
	if (status) { status.style.display = 'none'; }
	renderContacts(mockContacts);

	if (input)
	{
		var handler = debounce(function() {
			triggerContactsSearch();
		}, 300);
		input.addEventListener('input', handler);
	}
}

function triggerContactsSearch()
{
	var input = document.getElementById('contactSearch');
	var status = document.getElementById('contactsStatus');
	if (!input) { return; }

	var term = input.value.trim().toLowerCase();
	if (status) { status.innerHTML = 'Searching...'; status.style.display = 'block'; }

	// Mock search locally for frontend iteration
	var filtered = mockContacts.filter(function(c){
		return c.name.toLowerCase().indexOf(term) !== -1 ||
			c.email.toLowerCase().indexOf(term) !== -1 ||
			c.phone.toLowerCase().indexOf(term) !== -1;
	});

	setTimeout(function(){
		renderContacts(filtered);
	}, 200);
}
