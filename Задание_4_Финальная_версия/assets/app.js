document.addEventListener('DOMContentLoaded', () => {
  const checkIn = document.getElementById('check_in');
  const checkOut = document.getElementById('check_out');
  if (!checkIn || !checkOut) return;
  const periods = Array.isArray(window.approvedPeriods) ? window.approvedPeriods : [];
  const toDate = value => new Date(value + 'T00:00:00');
  function updateCheckout() {
    if (checkIn.value) {
      const next = new Date(toDate(checkIn.value).getTime() + 86400000);
      checkOut.min = next.toISOString().slice(0,10);
    }
  }
  checkIn.addEventListener('change', updateCheckout);
  updateCheckout();
  // Блокируем заезд в занятые даты; сервер всё равно повторно проверяет весь интервал.
  checkIn.addEventListener('input', () => {
    const blocked = periods.some(p => checkIn.value >= p.check_in && checkIn.value < p.check_out);
    checkIn.setCustomValidity(blocked ? 'На эту дату номер уже забронирован.' : '');
  });
  checkOut.addEventListener('input', () => {
    if (!checkIn.value || !checkOut.value) return;
    const overlap = periods.some(p => checkIn.value < p.check_out && checkOut.value > p.check_in);
    checkOut.setCustomValidity(overlap ? 'Период пересекается с подтверждённой бронью.' : '');
  });
});
