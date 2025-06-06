// Lớp Rectangle đại diện cho các ô trong bảng
class Rectangle {
    constructor(x, y, width, height, strokeColor = '#02075E') {
      this.x = x;          // Tọa độ X của hình chữ nhật
      this.y = y;          // Tọa độ Y của hình chữ nhật
      this.width = width;  // Chiều rộng của hình chữ nhật
      this.height = height; // Chiều cao của hình chữ nhật
      this.strokeColor = strokeColor; // Màu viền của hình chữ nhật
    }
  
    // Phương thức vẽ hình chữ nhật lên canvas
    draw(context) {
      context.fillStyle = 'transparent'; // Đặt nền trong suốt
      context.fillRect(this.x, this.y, this.width, this.height); // Vẽ hình chữ nhật (mặc dù là trong suốt)
  
      context.strokeStyle = this.strokeColor; // Đặt màu viền
      context.strokeRect(this.x, this.y, this.width, this.height); // Vẽ viền hình chữ nhật
    }
  }
  
  // Lớp Profile đại diện cho bảng profile
  class Profile {
    constructor(a, b, width, height, strokeColor = '#02075E') {
      this.a = a;
      this.b = b;
      this.width = width;
      this.height = height;
      this.strokeColor = strokeColor;
    }
  
    draw(context) {
      context.fillStyle = 'transparent'; // Đặt nền trong suốt
      context.fillRect(this.a, this.b, this.width, this.height); // Vẽ hình chữ nhật
      context.strokeStyle = this.strokeColor; // Đặt màu viền
      context.strokeRect(this.a, this.b, this.width, this.height); // Vẽ viền hình chữ nhật
    }
  }
  // Hàm tạo bảng với hàng, cột và kích thước hình chữ nhật tùy ý
  function createTable(rows, cols, rectWidth, rectHeight) {
    const canvas = document.getElementById('canvas'); // Lấy đối tượng canvas
    const context = canvas.getContext('2d'); // Lấy context để vẽ trên canvas
  
    const rowGap = 10; // Khoảng cách giữa các hàng
  
    // Tính toán lại chiều cao canvas để phù hợp với số hàng và khoảng cách
    canvas.height = rows * (rectHeight + rowGap) - rowGap;
    canvas.width = cols * rectWidth;
  
    // Vẽ các hình chữ nhật theo số hàng và cột
    for (let row = 0; row < rows; row++) {
      for (let col = 0; col < cols; col++) {
        // Kiểm tra nếu là hàng đầu tiên và trong 4 ô đầu thì bỏ qua
        if (row === 0 && col < 4) {
          continue; // Bỏ qua ô này
        }
  
        const x = col * rectWidth; // Tọa độ X dựa trên cột
        const y = row * (rectHeight + rowGap); // Tọa độ Y dựa trên hàng và khoảng cách giữa các hàng
        const rectangle = new Rectangle(x, y, rectWidth, rectHeight);
        rectangle.draw(context); // Vẽ hình chữ nhật
      }
    }
  }
  
  // Hàm tạo và vẽ bảng Profile trong khoảng trống 3 ô đầu tiên
  function createProfile(profileWidth, profileHeight) {
    const canvas = document.getElementById('canvas'); // Lấy đối tượng canvas
    const context = canvas.getContext('2d'); // Lấy context để vẽ trên canvas
  
    // Tính toán tọa độ để vẽ Profile vào vị trí 3 ô trống
    const profileX = 178;  // Tọa độ X (bắt đầu từ cột 0)
    const profileY = 0;  // Tọa độ Y (hàng đầu tiên)
  
    const profile = new Profile(profileX, profileY, profileWidth, profileHeight);
    profile.draw(context); // Vẽ Profile
  }
  
  // Gọi hàm để tạo bảng và bảng Profile
  const profileWidth = 3 * 175; // Kích thước chiều rộng của profile (tương ứng với 3 ô trống)
  const profileHeight = 241;    // Kích thước chiều cao của profile (bằng với chiều cao của 1 ô)
  
  const rows = 3; // Số hàng
  const cols = 10; // Số cột
  const rectWidth = 178; // Chiều rộng của mỗi hình chữ nhật
  const rectHeight = 241; // Chiều cao của mỗi hình chữ nhật
  
  // Vẽ profile trước
  
  // Sau đó vẽ bảng
  createTable(rows, cols, rectWidth, rectHeight);
  createProfile(profileWidth, profileHeight);
  
  