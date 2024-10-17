function GetCourses(uuid, name) {
    return `<option value="${uuid}">${name}</option>`;
}

function GetOrdersCard(courseName, orderDate, orderWeeks, author, ap, dp, total) {
    const div = document.createElement('div');
    div.className = 'ordersList';

    div.innerHTML = `
    <div class="orderDetails">
        <div class="orderInfo">
            <h2 id="txtCourseName">${courseName}</h2>
            <p id="txtOrderDate">${orderDate}</p>
            <p id="txtOrderWeek">${orderWeeks} weeks</p>
        </div>
        <p id="txtAuthor">by ${author}</p>
        <div class="paymentDetails">
            <h3>Payment details</h3>
            <div>
                <p>Course Fee</p>
                <p id="courseFeeAmount">${ap}</p>
            </div>
            <div>
                <p>Discount</p>
                <p id="courseFeeAmount">${dp}</p>
            </div>
            <hr>
            <div>
                <p>Total</p>
                <p id="courseFeeAmount">${total}</p>
            </div>
        </div>
        <div class="downloadInvoiceContainer">
            <button id="btnDownloadInvoice">Download Invoice</button>
        </div>
    </div>`;

    return div;
}